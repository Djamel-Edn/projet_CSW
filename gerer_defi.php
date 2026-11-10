<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: index.php?erreur=acces_refuse');
    exit();
}

require_once 'db.inc.php';
$id_user = $_SESSION['id_user'];
$id_defi = $_GET['id'] ?? null;

if (!$id_defi) {
    header('Location: ma_brute.php');
    exit();
}

// 1. Vérifier que le défi appartient bien à l'utilisateur courant
$sql_defi = "SELECT * FROM defis WHERE id_defi = :id_defi AND id_auteur = :id_auteur LIMIT 1";
$stmt_defi = $pdo->prepare($sql_defi);
$stmt_defi->execute([':id_defi' => $id_defi, ':id_auteur' => $id_user]);
$defi = $stmt_defi->fetch();

if (!$defi) {
    header('Location: ma_brute.php'); // Redirection si ce n'est pas son défi
    exit();
}

// 2. Récupérer les informations de la propre brute de l'auteur (pour comparer)
$sql_ma_brute = "SELECT * FROM brutes WHERE id_user = :id_user";
$stmt_ma_brute = $pdo->prepare($sql_ma_brute);
$stmt_ma_brute->execute([':id_user' => $id_user]);
$ma_brute = $stmt_ma_brute->fetch();

// 3. Récupérer toutes les candidatures en attente avec les stats de l'adversaire
$sql_candidats = "SELECT c.*, b.pseudo, b.avatar, b.force_brute, b.defense, b.victoires, b.defaites 
                  FROM candidatures c
                  JOIN brutes b ON c.id_candidat = b.id_user
                  WHERE c.id_defi = :id_defi AND c.etat = 'en_attente'";
$stmt_candidats = $pdo->prepare($sql_candidats);
$stmt_candidats->execute([':id_defi' => $id_defi]);
$candidats = $stmt_candidats->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Brawlers - Gérer mon défi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- NAV UNIFIÉE SANS LE BOUTON MA BRUTE -->
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom border-dark border-2">
    <div class="container">
        <!-- Le logo renvoie vers ma_brute si connecté, sinon vers index -->
        <a class="navbar-brand fw-bold" href="<?= isset($_SESSION['id_user']) ? 'ma_brute.php' : 'index.php' ?>">PIXEL BRAWLERS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
           <ul class="navbar-nav mx-auto">
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        
        <!-- Liste 1 : Admin -->
        <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="communaute.php">COMMUNAUTÉ</a></li>
        <li class="nav-item"><a class="nav-link text-danger fw-bold" href="admin.php">⚙️ ADMIN PANEL</a></li>
        
    <?php else: ?>
        
        <!-- Liste 2 : Visiteurs / Joueurs -->
        <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="index.php">ACCUEIL</a></li>
        <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="arene.php">ARÈNE</a></li>
        <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="communaute.php">COMMUNAUTÉ</a></li>
        <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="support.php">SUPPORT</a></li>
        
    <?php endif; ?>
</ul>
            
            <div class="d-flex align-items-center mt-3 mt-lg-0">
                <?php if (isset($_SESSION['id_user'])): ?>
                    <span class="me-3 fw-semibold small text-muted"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></span>
                    <a href="deconnexion.php" class="btn btn-outline-danger btn-sm fw-bold rounded-0">DÉCONNEXION</a>
                <?php else: ?>
                    <a href="inscription.php" class="btn btn-outline-dark btn-sm fw-bold rounded-0">S'INSCRIRE</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

    <!-- CONTENU -->
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-dark pb-3">
            <h2 class="fw-bold text-uppercase m-0">Défi : <?= htmlspecialchars($defi['titre']) ?></h2>
            <a href="ma_brute.php" class="btn btn-outline-dark rounded-0 fw-bold">Retour au profil</a>
        </div>

        <?php if ($defi['etat'] !== 'ouvert'): ?>
            <div class="alert alert-warning border-dark rounded-0 text-center fw-bold">
                Ce défi est terminé. Le combat a déjà eu lieu.
            </div>
        <?php else: ?>
            
            <p class="text-muted text-center mb-5">Consulte les caractéristiques des adversaires qui ont répondu à ton appel et choisis-en un pour le combat.</p>
            
            <div class="row g-4">
                <?php if (empty($candidats)): ?>
                    <div class="col-12">
                        <div class="alert alert-light border-dark text-center rounded-0 p-4">
                            Aucun joueur n'a encore osé répondre à ce défi. Reviens plus tard !
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($candidats as $c): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-dark rounded-0 h-100 shadow-sm">
                                <div class="card-header bg-dark text-white rounded-0 fw-bold text-center">
                                    CANDIDAT : <?= htmlspecialchars($c['pseudo']) ?>
                                </div>
                                <div class="card-body text-center">
                                    <div class="display-3 mb-2"><?= htmlspecialchars($c['avatar']) ?></div>
                                    <p class="fst-italic text-muted border border-secondary p-2 mb-3 bg-light">
                                        "<?= htmlspecialchars($c['message']) ?>"
                                    </p>
                                    
                                    <div class="d-flex justify-content-around mb-3 border-top border-bottom border-dark py-2">
                                        <div><strong class="d-block">⚔️ <?= $c['force_brute'] ?></strong><small>Force</small></div>
                                        <div><strong class="d-block">🛡️ <?= $c['defense'] ?></strong><small>Défense</small></div>
                                        <div><strong class="d-block text-success">🏆 <?= $c['victoires'] ?></strong><small>V</small></div>
                                    </div>
                                    
                                    <!-- Formulaire qui déclenchera le combat -->
                                    <form action="combat.php" method="POST">
                                        <input type="hidden" name="id_defi" value="<?= $defi['id_defi'] ?>">
                                        <input type="hidden" name="id_candidature" value="<?= $c['id_candidature'] ?>">
                                        <button type="submit" class="btn btn-danger w-100 rounded-0 fw-bold text-uppercase shadow-sm">
                                            💥 Affronter ce joueur
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>
</body>
</html>