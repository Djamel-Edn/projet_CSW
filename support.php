<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Brawlers - Support</title>
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
        <h2 class="fw-bold text-uppercase mb-4 text-center">Support & Règles</h2>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-dark rounded-0 shadow-sm p-4 mb-4">
                    <h5 class="fw-bold border-bottom border-dark pb-2">Comment se déroule un combat ?</h5>
                    <p class="text-muted">Les combats sont résolus automatiquement par l'Arène en fonction de vos statistiques de Force et de Défense, avec une part d'aléatoire pour représenter la chance des dés. L'auteur du défi choisit son adversaire parmi les candidatures.</p>
                    
                    <h5 class="fw-bold border-bottom border-dark pb-2 mt-4">Puis-je changer d'avatar ?</h5>
                    <p class="text-muted">Pour le moment, votre avatar et votre description sont définitifs une fois la brute créée. Réfléchissez bien avant d'entrer dans l'Arène !</p>

                    <h5 class="fw-bold border-bottom border-dark pb-2 mt-4">J'ai repéré un tricheur !</h5>
                    <p class="text-muted">Si un joueur utilise des propos injurieux dans la description de sa brute ou de ses défis, les Administrateurs ont le pouvoir de désactiver son compte et de supprimer ses provocations. Jouez fair-play.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>