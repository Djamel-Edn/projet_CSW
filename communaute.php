<?php
session_start();
require_once 'db.inc.php';

// Récupération du Top 10 des meilleures brutes
$sql_top = "SELECT pseudo, avatar, victoires, defaites FROM brutes ORDER BY victoires DESC LIMIT 10";
$top_brutes = $pdo->query($sql_top)->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Brawlers - Communauté</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

    <div class="container mt-5">
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold text-uppercase">Le Panthéon</h1>
            <p class="text-muted">Le classement des 10 guerriers les plus redoutables de l'Arène.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-dark rounded-0 shadow-sm">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center">Rang</th>
                                <th>Combattant</th>
                                <th class="text-center text-success">Victoires</th>
                                <th class="text-center text-danger">Défaites</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($top_brutes)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">Aucune brute n'a encore combattu.</td></tr>
                            <?php else: ?>
                                <?php foreach ($top_brutes as $index => $b): ?>
                                    <tr>
                                        <td class="text-center align-middle fw-bold fs-5">#<?= $index + 1 ?></td>
                                        <td class="align-middle">
                                            <span class="fs-4 me-2"><?= htmlspecialchars($b['avatar']) ?></span>
                                            <span class="fw-bold"><?= htmlspecialchars($b['pseudo']) ?></span>
                                        </td>
                                        <td class="text-center align-middle text-success fw-bold"><?= $b['victoires'] ?></td>
                                        <td class="text-center align-middle text-danger fw-bold"><?= $b['defaites'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>