<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: index.php?erreur=acces_refuse');
    exit();
}

require_once 'db.inc.php';

$id_user = $_SESSION['id_user'];
$id_defi = $_GET['id'] ?? null;
$erreur = '';
$succes = '';

if (!$id_defi) {
    header('Location: index.php');
    exit();
}

// 1. Récupération des informations du défi et de son auteur
$sql_defi = "SELECT d.*, b.pseudo, b.avatar 
             FROM defis d
             JOIN brutes b ON d.id_auteur = b.id_user
             WHERE d.id_defi = :id_defi LIMIT 1";
$stmt_defi = $pdo->prepare($sql_defi);
$stmt_defi->execute([':id_defi' => $id_defi]);
$defi = $stmt_defi->fetch();

if (!$defi) {
    header('Location: index.php');
    exit();
}

// 2. Vérification : l'utilisateur courant possède-t-il une brute ?
$sql_ma_brute = "SELECT id_brute FROM brutes WHERE id_user = :id_user LIMIT 1";
$stmt_ma_brute = $pdo->prepare($sql_ma_brute);
$stmt_ma_brute->execute([':id_user' => $id_user]);
$ma_brute = $stmt_ma_brute->fetch();

// 3. Vérification de la candidature : on récupère aussi l'état (en_attente, acceptee, refusee)
$sql_check_candidature = "SELECT id_candidature, etat FROM candidatures WHERE id_defi = :id_defi AND id_candidat = :id_user LIMIT 1";
$stmt_check_candidature = $pdo->prepare($sql_check_candidature);
$stmt_check_candidature->execute([':id_defi' => $id_defi, ':id_user' => $id_user]);
$deja_postule = $stmt_check_candidature->fetch();

// 4. Traitement du formulaire de candidature
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['postuler'])) {
    $message = trim($_POST['message'] ?? '');

    if (!$ma_brute) {
        $erreur = "Tu dois créer une brute avant de pouvoir combattre !";
    } elseif ($defi['id_auteur'] == $id_user) {
        $erreur = "Tu ne peux pas répondre à ton propre défi.";
    } elseif ($deja_postule) {
        $erreur = "Tu as déjà postulé à ce défi.";
    } elseif ($defi['etat'] !== 'ouvert') {
        $erreur = "Ce défi n'est plus ouvert.";
    } else {
        $sql_insert = "INSERT INTO candidatures (id_defi, id_candidat, message) VALUES (:id_defi, :id_candidat, :message)";
        $stmt_insert = $pdo->prepare($sql_insert);
        try {
            $stmt_insert->execute([
                ':id_defi' => $id_defi,
                ':id_candidat' => $id_user,
                ':message' => $message
            ]);
            $succes = "Ta candidature a été envoyée ! L'auteur du défi va maintenant choisir son adversaire.";
            // On simule le rechargement de la candidature pour l'affichage immédiat
            $deja_postule = ['etat' => 'en_attente']; 
        } catch (PDOException $e) {
            $erreur = "Erreur lors de la candidature : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Brawlers - Détails du Défi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

   
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

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                
                <div class="card border-dark rounded-0 shadow-sm mb-4">
                    <div class="card-header bg-dark text-white rounded-0 fw-bold text-uppercase">
                        Détails du défi
                    </div>
                    <div class="card-body text-center p-4">
                        <div class="display-1 mb-3"><?= htmlspecialchars($defi['avatar']) ?></div>
                        <h3 class="fw-bold text-uppercase"><?= htmlspecialchars($defi['titre']) ?></h3>
                        <h6 class="text-muted mb-4">Provoqué par la brute <?= htmlspecialchars($defi['pseudo']) ?></h6>
                        <div class="p-3 bg-light border border-dark fst-italic">
                            "<?= nl2br(htmlspecialchars($defi['description'])) ?>"
                        </div>
                    </div>
                </div>

                <div class="card border-dark rounded-0 shadow-sm mb-5">
                    <div class="card-body p-4">
                        <?php if (!empty($erreur)): ?>
                            <div class="alert alert-danger rounded-0"><?= htmlspecialchars($erreur) ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($succes)): ?>
                            <div class="alert alert-success rounded-0"><?= htmlspecialchars($succes) ?></div>
                            <div class="text-center"><a href="index.php" class="btn btn-dark rounded-0 fw-bold">Retour à l'Arène</a></div>
                        <?php else: ?>
                            
                            <!-- AFFICHAGE DYNAMIQUE SELON LE STATUT DU DÉFI ET DE LA CANDIDATURE -->
                            <?php if ($defi['etat'] === 'termine'): ?>
                                
                                <?php if ($defi['id_auteur'] == $id_user): ?>
                                    <div class="alert alert-dark border-dark rounded-0 text-center mb-0">
                                        <h5 class="fw-bold text-uppercase">Combat terminé</h5>
                                        <p class="fst-italic bg-white p-3 border border-dark mt-2 mb-0">
                                            <?= nl2br(htmlspecialchars($defi['rapport_combat'])) ?>
                                        </p>
                                    </div>
                                    
                                <?php elseif ($deja_postule && $deja_postule['etat'] === 'acceptee'): ?>
                                    <div class="alert alert-info border-dark rounded-0 text-center mb-0">
                                        <h5 class="fw-bold text-uppercase text-primary">Tu as été sélectionné pour ce combat !</h5>
                                        <p class="mb-2">Voici le compte-rendu de l'affrontement :</p>
                                        <p class="fst-italic bg-white p-3 border border-dark mb-0 text-dark">
                                            <?= nl2br(htmlspecialchars($defi['rapport_combat'])) ?>
                                        </p>
                                    </div>
                                    
                                <?php elseif ($deja_postule && $deja_postule['etat'] === 'refusee'): ?>
                                    <div class="alert alert-secondary border-dark rounded-0 text-center mb-0">
                                        L'auteur du défi a choisi un autre adversaire. Reste sur tes gardes pour le prochain combat !
                                    </div>
                                    
                                <?php else: ?>
                                    <div class="alert alert-warning border-dark rounded-0 text-center mb-0">
                                        Ce défi est clos. Le sang a déjà coulé dans l'Arène.
                                    </div>
                                <?php endif; ?>

                            <?php elseif ($defi['id_auteur'] == $id_user): ?>
                                <div class="alert alert-info border-dark rounded-0 text-center mb-0">
                                    <strong>C'est ton propre défi.</strong><br> Rends-toi sur ton profil pour voir qui a osé te répondre !
                                </div>
                                <div class="text-center mt-3">
                                    <a href="ma_brute.php" class="btn btn-dark rounded-0 fw-bold">Gérer mes défis</a>
                                </div>
                            
                            <?php elseif (!$ma_brute): ?>
                                <div class="alert alert-warning border-dark rounded-0 text-center mb-0">
                                    Tu dois d'abord créer ta brute pour pouvoir postuler.
                                </div>
                                <div class="text-center mt-3">
                                    <a href="ma_brute.php" class="btn btn-dark rounded-0 fw-bold">Créer ma brute</a>
                                </div>
                            
                            <?php elseif ($deja_postule): ?>
                                <div class="alert alert-success border-dark rounded-0 text-center mb-0">
                                    Ta provocation est envoyée. Si l'auteur t'accepte, le combat éclatera.
                                </div>
                            
                            <?php else: ?>
                                <h5 class="fw-bold mb-3 text-center">Accepter le défi</h5>
                                <form action="voir_defi.php?id=<?= $id_defi ?>" method="POST">
                                    <div class="mb-3">
                                        <label for="message" class="form-label fw-semibold">Message de provocation (optionnel)</label>
                                        <input type="text" class="form-control border-dark rounded-0" id="message" name="message" placeholder="Ex: Tu vas mordre la poussière...">
                                    </div>
                                    <button type="submit" name="postuler" class="btn btn-danger w-100 rounded-0 fw-bold text-uppercase">Envoyer ma Brute au combat</button>
                                </form>
                            <?php endif; ?>
                            
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>