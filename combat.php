<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: index.php');
    exit();
}

require_once 'db.inc.php';

$id_user = $_SESSION['id_user'];
$id_defi = $_POST['id_defi'] ?? null;
$id_candidature = $_POST['id_candidature'] ?? null;

$erreur = '';
$rapport = '';
$id_vainqueur = null;
$brute_auteur = null;
$brute_candidat = null;

if (!$id_defi || !$id_candidature) {
    $erreur = "Informations manquantes pour lancer le combat.";
} else {
    // 1. Vérification : le défi appartient-il à l'utilisateur et est-il bien ouvert ?
    $sql_defi = "SELECT * FROM defis WHERE id_defi = :id_defi AND id_auteur = :id_auteur AND etat = 'ouvert' LIMIT 1";
    $stmt_defi = $pdo->prepare($sql_defi);
    $stmt_defi->execute([':id_defi' => $id_defi, ':id_auteur' => $id_user]);
    $defi = $stmt_defi->fetch();

    if (!$defi) {
        $erreur = "Ce combat a déjà eu lieu ou le défi ne t'appartient pas.";
    } else {
        // 2. Récupération de la brute de l'auteur (Joueur 1)
        $sql_auteur = "SELECT * FROM brutes WHERE id_user = :id_user";
        $stmt_auteur = $pdo->prepare($sql_auteur);
        $stmt_auteur->execute([':id_user' => $id_user]);
        $brute_auteur = $stmt_auteur->fetch();

        // 3. Récupération de la brute du candidat sélectionné (Joueur 2)
        $sql_candidat = "SELECT b.*, c.id_candidat FROM candidatures c 
                         JOIN brutes b ON c.id_candidat = b.id_user 
                         WHERE c.id_candidature = :id_candidature AND c.id_defi = :id_defi";
        $stmt_candidat = $pdo->prepare($sql_candidat);
        $stmt_candidat->execute([':id_candidature' => $id_candidature, ':id_defi' => $id_defi]);
        $brute_candidat = $stmt_candidat->fetch();

        if (!$brute_auteur || !$brute_candidat) {
            $erreur = "Impossible de trouver les combattants dans l'Arène.";
        } else {
            // 4. SIMULATION DU COMBAT
            $chance_auteur = rand(1, 10);
            $chance_candidat = rand(1, 10);

            $score_auteur = ($brute_auteur['force_brute'] * 1.5) + ($brute_auteur['defense'] * $chance_auteur);
            $score_candidat = ($brute_candidat['force_brute'] * 1.5) + ($brute_candidat['defense'] * $chance_candidat);

            if ($score_auteur >= $score_candidat) {
                $id_vainqueur = $brute_auteur['id_user'];
                $id_perdant = $brute_candidat['id_user'];
                $nom_vainqueur = $brute_auteur['pseudo'];
            } else {
                $id_vainqueur = $brute_candidat['id_user'];
                $id_perdant = $brute_auteur['id_user'];
                $nom_vainqueur = $brute_candidat['pseudo'];
            }

            $rapport = "Le combat fut d'une violence inouïe !\n";
            $rapport .= "{$brute_auteur['pseudo']} a frappé avec une intensité de " . round($score_auteur) . ".\n";
            $rapport .= "{$brute_candidat['pseudo']} a riposté avec une intensité de " . round($score_candidat) . ".\n";
            $rapport .= "Vainqueur final : **$nom_vainqueur** !";

            // 5. MISES À JOUR DANS LA BASE DE DONNÉES (Transaction)
            try {
                $pdo->beginTransaction();

                $sql_update_defi = "UPDATE defis SET etat = 'termine', id_vainqueur = :id_vainqueur, rapport_combat = :rapport WHERE id_defi = :id_defi";
                $pdo->prepare($sql_update_defi)->execute([':id_vainqueur' => $id_vainqueur, ':rapport' => $rapport, ':id_defi' => $id_defi]);

                $sql_accept = "UPDATE candidatures SET etat = 'acceptee' WHERE id_candidature = :id_candidature";
                $pdo->prepare($sql_accept)->execute([':id_candidature' => $id_candidature]);

                $sql_reject = "UPDATE candidatures SET etat = 'refusee' WHERE id_defi = :id_defi AND id_candidature != :id_candidature";
                $pdo->prepare($sql_reject)->execute([':id_defi' => $id_defi, ':id_candidature' => $id_candidature]);

                $sql_win = "UPDATE brutes SET victoires = victoires + 1 WHERE id_user = :id_vainqueur";
                $pdo->prepare($sql_win)->execute([':id_vainqueur' => $id_vainqueur]);

                $sql_lose = "UPDATE brutes SET defaites = defaites + 1 WHERE id_user = :id_perdant";
                $pdo->prepare($sql_lose)->execute([':id_perdant' => $id_perdant]);

                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $erreur = "Erreur lors de l'enregistrement du combat : " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Brawlers - Résultat du Combat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- NAV UNIFIÉE -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom border-dark border-2">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">PIXEL BRAWLERS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="index.php">ACCUEIL</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="arene.php">ARÈNE</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="communaute.php">COMMUNAUTÉ</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="support.php">SUPPORT</a></li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link text-danger fw-bold" href="admin.php">⚙️ ADMIN</a></li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center mt-3 mt-lg-0">
                    <a href="ma_brute.php" class="btn btn-dark btn-sm fw-bold rounded-0 me-2">MA BRUTE</a>
                    <a href="deconnexion.php" class="btn btn-outline-danger btn-sm fw-bold rounded-0">DÉCONNEXION</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- CONTENU PRINCIPAL -->
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-dark rounded-0 shadow-sm text-center">
                    
                    <div class="card-header bg-dark text-white rounded-0 fw-bold display-6 py-3 text-uppercase">
                        💥 Résultat du Combat
                    </div>
                    
                    <div class="card-body p-5">
                        <?php if (!empty($erreur)): ?>
                            <div class="alert alert-danger rounded-0 border-dark shadow-sm">
                                <strong>Halte !</strong> <?= htmlspecialchars($erreur) ?>
                            </div>
                            <a href="ma_brute.php" class="btn btn-dark fw-bold rounded-0 px-4 mt-3">Retour au profil</a>
                        <?php else: ?>
                            
                            <div class="row mb-4 align-items-center">
                                <div class="col-5">
                                    <div class="display-3"><?= htmlspecialchars($brute_auteur['avatar']) ?></div>
                                    <h4 class="fw-bold text-uppercase mt-2"><?= htmlspecialchars($brute_auteur['pseudo']) ?></h4>
                                </div>
                                <div class="col-2 display-5 fw-bold text-danger">VS</div>
                                <div class="col-5">
                                    <div class="display-3"><?= htmlspecialchars($brute_candidat['avatar']) ?></div>
                                    <h4 class="fw-bold text-uppercase mt-2"><?= htmlspecialchars($brute_candidat['pseudo']) ?></h4>
                                </div>
                            </div>

                            <div class="alert alert-light border border-dark rounded-0 text-start fst-italic p-4 mb-4 shadow-sm">
                                <?= nl2br(htmlspecialchars($rapport)) ?>
                            </div>

                            <?php if ($id_vainqueur == $id_user): ?>
                                <h2 class="text-success fw-bold text-uppercase">Tu as remporté la victoire ! 🏆</h2>
                            <?php else: ?>
                                <h2 class="text-danger fw-bold text-uppercase">Tu as mordu la poussière... 💀</h2>
                            <?php endif; ?>

                            <div class="mt-5">
                                <a href="arene.php" class="btn btn-dark fw-bold rounded-0 px-5 me-2">L'ARÈNE</a>
                                <a href="ma_brute.php" class="btn btn-outline-dark fw-bold rounded-0 px-4">MON PROFIL</a>
                            </div>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>