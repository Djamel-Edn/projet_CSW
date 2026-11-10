<?php
session_start();

// Protection de la page : si l'utilisateur n'est pas connecté, on le renvoie à l'accueil
if (!isset($_SESSION['id_user'])) {
    header('Location: index.php');
    exit();
}

require_once 'db.inc.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Brawlers - Ma Brute</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- HEADER CONNECTÉ -->
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
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="#">NOUVELLES</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="#">SUPPORT</a></li>
                </ul>
                
                <!-- Gestion de l'utilisateur connecté -->
                <div class="d-flex align-items-center mt-3 mt-lg-0">
                    <span class="me-3 fw-semibold"><?= htmlspecialchars($_SESSION['email']) ?></span>
                    <a href="deconnexion.php" class="btn btn-outline-danger btn-sm fw-bold rounded-0">DÉCONNEXION</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- CONTENU PRINCIPAL -->
    <div class="container mt-5">
        <h1 class="display-5 fw-light mb-4 text-uppercase">Ma Brute</h1>
        
        <div class="card rounded-0 border-dark shadow-sm">
            <div class="card-body p-5 text-center">
                <h3 class="fw-bold">Bienvenue dans l'Arène !</h3>
                <p class="text-muted">Ta session fonctionne parfaitement.</p>
                <p></p>
            </div>
        </div>
    </div>

  
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>