<?php
session_start();
require_once 'db.inc.php';

// Récupération des défis ouverts
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
    <title>Pixel Brawlers - L'Arène</title>
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
                    <?php if (isset($_SESSION['id_user'])): ?>
                        <a href="ma_brute.php" class="btn btn-dark btn-sm fw-bold rounded-0 me-2">MA BRUTE</a>
                        <a href="deconnexion.php" class="btn btn-outline-danger btn-sm fw-bold rounded-0">DÉCONNEXION</a>
                    <?php else: ?>
                        <a href="inscription.php" class="btn btn-outline-dark btn-sm fw-bold rounded-0">S'INSCRIRE</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- CONTENU DE L'ARÈNE -->
    <div class="container mt-5 mb-5">
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold text-uppercase">L'Arène</h1>
            <p class="text-muted">Choisis ton adversaire et prouve ta valeur.</p>
        </div>
        
        <div class="row g-4">
            <?php if (empty($defis_ouverts)): ?>
                <div class="col-12">
                    <div class="alert alert-light border-dark text-center rounded-0 p-5" role="alert">
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