<?php
session_start();

if (isset($_SESSION['id_user'])) {
    header('Location: ma_brute.php');
    exit();
}

require_once 'db.inc.php';

$erreur = '';

// Interception de l'erreur de redirection (protection des pages)
if (isset($_GET['erreur']) && $_GET['erreur'] === 'acces_refuse') {
    $erreur = "Accès refusé : Vous devez être connecté pour accéder à cette page.";
}

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
                $erreur = "Le compte a été désactivé par un administrateur.";
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
            $erreur = "Aucun compte trouvé avec cet email.";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}

// APERÇU POUR LES VISITEURS (Critère de la grille)
// 1. Les 3 derniers défis
$sql_defis = "SELECT d.id_defi, d.titre, b.pseudo, b.avatar 
              FROM defis d JOIN brutes b ON d.id_auteur = b.id_user 
              WHERE d.etat = 'ouvert' ORDER BY d.id_defi DESC LIMIT 3";
$defis_apercu = $pdo->query($sql_defis)->fetchAll();

// 2. Les 3 meilleures brutes
$sql_brutes = "SELECT id_user, pseudo, avatar, victoires 
               FROM brutes ORDER BY victoires DESC LIMIT 3";
$brutes_apercu = $pdo->query($sql_brutes)->fetchAll();
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
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom border-dark border-2">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">PIXEL BRAWLERS</a>
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
                    <a href="inscription.php" class="btn btn-outline-dark btn-sm fw-bold rounded-0">S'INSCRIRE</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4 mb-5">
        
        <?php if (!empty($erreur)): ?>
            <div class="alert alert-danger rounded-0 fw-bold shadow-sm text-center mb-4" role="alert">
                <?= htmlspecialchars($erreur) ?>
            </div>
        <?php endif; ?>

        <!-- BANNIÈRE & CONNEXION -->
        <div class="row g-4 mb-5">
            <div class="col-lg-8">
                <div class="hero-banner flex-column position-relative shadow-sm text-center">
                    <h1 class="display-4 fw-bold mb-3">PIXEL BRAWLERS</h1>
                    <p class="text-muted px-4 mb-4">Créez votre brute et affrontez d'autres joueurs dans l'Arène !</p>
                    <a href="inscription.php" class="btn btn-dark border-dark rounded-0 px-4 py-2 text-white fw-bold">CRÉER UN COMPTE</a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-dark rounded-0 shadow-sm p-4 h-100">
                    <h5 class="fw-bold mb-4 text-center">CONNEXION</h5>
                    <form action="index.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Email</label>
                            <input type="email" class="form-control border-dark rounded-0" name="identifiant" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Mot de passe</label>
                            <input type="password" class="form-control border-dark rounded-0" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-dark w-100 rounded-0 fw-bold">JOUER</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- APERÇU DES DONNÉES (POUR LE CORRECTEUR) -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-dark rounded-0 shadow-sm h-100">
                    <div class="card-header bg-dark text-white rounded-0 fw-bold text-uppercase">Derniers défis ouverts</div>
                    <div class="list-group list-group-flush rounded-0">
                        <?php if (empty($defis_apercu)): ?>
                            <div class="p-3 text-muted fst-italic text-center">Aucun défi en cours.</div>
                        <?php else: ?>
                            <?php foreach ($defis_apercu as $d): ?>
                                <div class="list-group-item border-dark d-flex align-items-center p-3">
                                    <span class="fs-3 me-3"><?= htmlspecialchars($d['avatar']) ?></span>
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($d['titre']) ?></h6>
                                        <small class="text-muted">Par <?= htmlspecialchars($d['pseudo']) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-dark rounded-0 shadow-sm h-100">
                    <div class="card-header bg-dark text-white rounded-0 fw-bold text-uppercase">Top 3 des Brutes</div>
                    <div class="list-group list-group-flush rounded-0">
                        <?php if (empty($brutes_apercu)): ?>
                            <div class="p-3 text-muted fst-italic text-center">Aucune brute inscrite.</div>
                        <?php else: ?>
                            <?php foreach ($brutes_apercu as $b): ?>
                                <a href="voir_brute.php?id=<?= $b['id_user'] ?>" class="list-group-item list-group-item-action border-dark d-flex align-items-center justify-content-between p-3">
                                    <div class="d-flex align-items-center">
                                        <span class="fs-3 me-3"><?= htmlspecialchars($b['avatar']) ?></span>
                                        <h6 class="mb-0 fw-bold text-uppercase"><?= htmlspecialchars($b['pseudo']) ?></h6>
                                    </div>
                                    <span class="badge bg-success rounded-0"><?= $b['victoires'] ?> Victoires</span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</body>
</html>