<!DOCTYPE html>
<html lang="fr">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Brawlers - Accueil</title>
    <!-- Intégration de Bootstrap (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Quelques styles pour se rapprocher de la maquette filaire */
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
        .brute-card {
            border: 1px solid #333;
            height: 200px;
            background: linear-gradient(to bottom, #f8f9fa 50%, #d6d8db 100%);
        }
    </style>
</head>
<body>

    <!-- HEADER / NAVIGATION -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom border-dark border-2">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">PIXEL BRAWLERS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="#">ACCUEIL</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="#">JEUX</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="#">COMMUNAUTÉ</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="#">NOUVELLES</a></li>
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="#">SUPPORT</a></li>
                </ul>
                <a href="#" class="btn btn-outline-dark fw-bold rounded-0">JOUER MAINTENANT</a>
            </div>
        </div>
    </nav>

    <!-- SECTION PRINCIPALE : BANNIÈRE & CONNEXION -->
    <div class="container mt-4">
        <div class="row g-4">
            
            <!-- Colonne Gauche : Bannière -->
            <div class="col-lg-8">
                <div class="hero-banner flex-column position-relative">
                    <h1 class="display-4 fw-bold mb-4">PIXEL BRAWLERS</h1>
                    <button class="btn btn-secondary border-dark rounded-0 px-4 py-2 text-dark" style="background-color:#dcdcdc;">JOUER</button>
                    <!-- Espace pour expliquer les règles comme prévu dans le doc -->
                    <p class="mt-4 text-muted px-4 text-center">Inscrivez-vous, créez votre brute et affrontez d'autres joueurs dans l'Arène !</p>
                </div>
            </div>

            <!-- Colonne Droite : Formulaire de connexion -->
            <div class="col-lg-4">
                <div class="login-box">
                    <h5 class="fw-bold mb-4">CONNEXION JOUEUR</h5>
                    <!-- Le formulaire pointera vers un script PHP plus tard -->
                    <form action="index.php" method="POST">
                        <div class="mb-3">
                            <label for="identifiant" class="form-label fw-semibold" style="font-size: 0.9rem;">Nom d'utilisateur / Email</label>
                            <input type="text" class="form-control border-dark rounded-0" id="identifiant" name="identifiant" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold" style="font-size: 0.9rem;">Mot de passe</label>
                            <input type="password" class="form-control border-dark rounded-0" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-secondary w-100 border-dark rounded-0 text-dark fw-bold" style="background-color:#dcdcdc;">CONNEXION</button>
                        
                        <div class="mt-3 text-center">
                            <a href="#" class="text-dark" style="font-size: 0.85rem;">Pas encore de compte ? S'inscrire</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION : DERNIÈRES BRUTES & MISES À JOUR -->
    <div class="container mt-5 mb-5">
        <h5 class="fw-bold mb-3">DERNIÈRES BRUTES & MISES À JOUR</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="brute-card"></div>
            </div>
            <div class="col-md-4">
                <div class="brute-card"></div>
            </div>
            <div class="col-md-4">
                <div class="brute-card"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>