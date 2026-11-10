<?php
// On démarre la session avant tout code HTML
session_start();

// Si l'utilisateur est déjà connecté, on le redirige vers l'espace joueur
if (isset($_SESSION['id_user'])) {
    header('Location: ma_brute.php');
    exit();
}

require_once 'db.inc.php';

$erreur = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($identifiant) && !empty($password)) {
        $sql = "SELECT id_user, email, mot_de_passe, role, actif FROM utilisateurs WHERE email = :email LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $identifiant]);
        $user = $stmt->fetch();

        if ($user !== false) {
            if ($user['actif'] != 1) {
                $erreur = "Le compte a été désactivé dans la base de données.";
            } elseif (!password_verify($password, $user['mot_de_passe'])) {
                $erreur = "Le mot de passe est incorrect.";
            } else {
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                header('Location: ma_brute.php');
                exit();
            }
        } else {
            $erreur = "Aucun compte trouvé avec cet email. Vérifiez l'orthographe.";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}

// ---------------------------------------------------------
// NOUVEAU : Récupération des défis ouverts pour l'affichage
// ---------------------------------------------------------
$sql_defis = "SELECT d.id_defi, d.titre, d.description, b.pseudo, b.avatar 
              FROM defis d
              JOIN brutes b ON d.id_auteur = b.id_user
              WHERE d.etat = 'ouvert'
              ORDER BY d.id_defi DESC";
$stmt_defis = $pdo->query($sql_defis);
$defis_ouverts = $stmt_defis->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Brawlers - Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero-banner {
            background-color: #f0f0f0;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ccc;
        }
        .login-box {
            border: 2px solid #333;
            padding: 20px;
            background-color: #fff;
        }
    </style>
</head>
<body class="bg-light">

    <!-- HEADER / NAVIGATION -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom border-dark border-2">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">PIXEL BRAWLERS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="index.php">ACCUEIL</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="#">JEUX</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="#">COMMUNAUTÉ</a></li>
                </ul>
                <a href="inscription.php" class="btn btn-outline-dark fw-bold rounded-0">S'INSCRIRE</a>
            </div>
        </div>
    </nav>

    <!-- BANNIÈRE & CONNEXION -->
    <div class="container mt-4">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="hero-banner flex-column position-relative shadow-sm">
                    <h1 class="display-4 fw-bold mb-4">PIXEL BRAWLERS</h1>
                    <a href="inscription.php" class="btn btn-dark border-dark rounded-0 px-4 py-2 text-white fw-bold">JOUER</a>
                    <p class="mt-4 text-muted px-4 text-center">Inscrivez-vous, créez votre brute et affrontez d'autres joueurs dans l'Arène !</p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="login-box shadow-sm">
                    <h5 class="fw-bold mb-4 text-center">CONNEXION JOUEUR</h5>
                    
                    <?php if (!empty($erreur)): ?>
                        <div class="alert alert-danger p-2 mb-3" style="font-size: 0.85rem;" role="alert">
                            <?= htmlspecialchars($erreur) ?>
                        </div>
                    <?php endif; ?>

                    <form action="index.php" method="POST">
                        <div class="mb-3">
                            <label for="identifiant" class="form-label fw-semibold" style="font-size: 0.9rem;">Email</label>
                            <input type="email" class="form-control border-dark rounded-0" id="identifiant" name="identifiant" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold" style="font-size: 0.9rem;">Mot de passe</label>
                            <input type="password" class="form-control border-dark rounded-0" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-dark w-100 border-dark rounded-0 fw-bold">CONNEXION</button>
                        
                        <div class="mt-3 text-center">
                            <a href="inscription.php" class="text-dark" style="font-size: 0.85rem;">Pas encore de compte ? S'inscrire</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION : DÉFIS EN COURS DANS L'ARÈNE -->
    <div class="container mt-5 mb-5">
        <h5 class="fw-bold mb-4 text-uppercase border-bottom border-dark pb-2">Défis en cours dans l'Arène</h5>
        
        <div class="row g-4">
            <?php if (empty($defis_ouverts)): ?>
                <div class="col-12">
                    <div class="alert alert-light border-dark text-center rounded-0" role="alert">
                        L'arène est calme... Aucun défi n'est ouvert pour le moment.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($defis_ouverts as $defi): ?>
                    <div class="col-md-4">
                        <div class="card border-dark rounded-0 h-100 shadow-sm">
                            <div class="card-body text-center">
                                <div class="display-4 mb-2"><?= htmlspecialchars($defi['avatar']) ?></div>
                                <h5 class="card-title fw-bold text-uppercase"><?= htmlspecialchars($defi['titre']) ?></h5>
                                <h6 class="card-subtitle mb-3 text-muted">Lancé par <?= htmlspecialchars($defi['pseudo']) ?></h6>
                                <p class="card-text small fst-italic">"<?= htmlspecialchars($defi['description']) ?>"</p>
                            </div>
                            <div class="card-footer bg-white border-dark border-top-0 text-center pb-3 pt-0">
                                <!-- Le bouton de réponse pointera vers le traitement de la candidature -->
                                <a href="voir_defi.php?id=<?= $defi['id_defi'] ?>" class="btn btn-outline-dark fw-bold rounded-0 btn-sm w-100">DÉTAILS DU DÉFI</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>