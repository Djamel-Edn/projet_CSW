<?php
session_start();
require_once 'db.inc.php';

// Récupération de l'ID du joueur ciblé
$id_cible = $_GET['id'] ?? null;

if (!$id_cible) {
    header('Location: communaute.php');
    exit();
}

// Récupération des données de la brute
$sql = "SELECT * FROM brutes WHERE id_user = :id LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id_cible]);
$brute = $stmt->fetch();

// Si la brute n'existe pas, on redirige
if (!$brute) {
    header('Location: communaute.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Brawlers - Fiche Brute</title>
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

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-dark rounded-0 shadow-sm">
                    <div class="card-header bg-dark text-white rounded-0 fw-bold text-center text-uppercase py-3">
                        Fiche Combattant
                    </div>
                    <div class="card-body p-4 text-center">
                        <div class="display-1 mb-3"><?= htmlspecialchars($brute['avatar']) ?></div>
                        <h3 class="fw-bold text-uppercase"><?= htmlspecialchars($brute['pseudo']) ?></h3>
                        <p class="text-muted fst-italic">"<?= htmlspecialchars($brute['description']) ?>"</p>
                        <hr class="border-dark">
                        
                        <div class="mb-3 text-center">
                            <h4 class="fw-bold text-danger mb-0">❤️ <?= $brute['pv'] ?> / 30</h4>
                            <small class="text-muted text-uppercase fw-bold">Points de vie</small>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-6 mb-3"><h5 class="fw-bold mb-0">⚔️ <?= $brute['force_brute'] ?></h5><small class="text-muted">FORCE</small></div>
                            <div class="col-6 mb-3"><h5 class="fw-bold mb-0">🛡️ <?= $brute['defense'] ?></h5><small class="text-muted">DÉFENSE</small></div>
                            <div class="col-6 mb-3"><h5 class="fw-bold mb-0 text-success">🏆 <?= $brute['victoires'] ?></h5><small class="text-muted">VICTOIRES</small></div>
                            <div class="col-6 mb-3"><h5 class="fw-bold mb-0 text-danger">💀 <?= $brute['defaites'] ?></h5><small class="text-muted">DÉFAITES</small></div>
                        </div>

                        <div class="mt-4">
                            <!-- Permet de revenir à la page précédente (Arène ou Communauté) -->
                            <a href="javascript:history.back()" class="btn btn-outline-dark fw-bold rounded-0 px-4 w-100 text-uppercase">Retour</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>