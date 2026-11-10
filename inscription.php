<?php
session_start();

// Si l'utilisateur est déjà connecté, on le redirige
if (isset($_SESSION['id_user'])) {
    header('Location: ma_brute.php');
    exit();
}

require_once 'db.inc.php';

$erreur = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (!empty($email) && !empty($password) && !empty($password_confirm)) {
        if ($password === $password_confirm) {
            
            // 1. Vérification si l'email existe déjà (Critère de la grille)
            $sql_check = "SELECT id_user FROM utilisateurs WHERE email = :email";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([':email' => $email]);
            
            if ($stmt_check->fetch()) {
                $erreur = "Cet email est déjà utilisé. Veuillez en choisir un autre ou vous connecter.";
            } else {
                // 2. Hachage du mot de passe en Bcrypt/Blowfish (Critère de la grille)
                $mdp_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // 3. Insertion sans choix du rôle, le rôle par défaut 'joueur' s'applique (Critère de la grille)
                $sql_insert = "INSERT INTO utilisateurs (email, mot_de_passe) VALUES (:email, :mdp)";
                $stmt_insert = $pdo->prepare($sql_insert);
                
                try {
                    $stmt_insert->execute([
                        ':email' => $email,
                        ':mdp' => $mdp_hash
                    ]);
                    $succes = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                } catch (PDOException $e) {
                    $erreur = "Erreur lors de l'inscription : " . $e->getMessage();
                }
            }
        } else {
            $erreur = "Les mots de passe ne correspondent pas.";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Brawlers - Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .register-box {
            border: 2px solid #333;
            padding: 30px;
            background-color: #fff;
        }
    </style>
</head>
<body class="bg-light">

    <!-- HEADER / NAVIGATION (Mise en page commune - Critère de la grille) -->
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
                <a href="index.php" class="btn btn-outline-dark fw-bold rounded-0">CONNEXION</a>
            </div>
        </div>
    </nav>

    <!-- FORMULAIRE D'INSCRIPTION ADAPTATIF (Critère vue mobile) -->
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                <div class="register-box shadow-sm">
                    <h4 class="fw-bold mb-4 text-center">CRÉER UN COMPTE</h4>

                    <!-- Messages de retour (Critères de la grille) -->
                    <?php if (!empty($erreur)): ?>
                        <div class="alert alert-danger rounded-0" role="alert">
                            <?= htmlspecialchars($erreur) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($succes)): ?>
                        <div class="alert alert-success rounded-0" role="alert">
                            <?= htmlspecialchars($succes) ?> <br>
                            <a href="index.php" class="fw-bold text-success">Aller à la connexion</a>
                        </div>
                    <?php else: ?>
                        <!-- On masque le formulaire si l'inscription est réussie -->
                        <form action="inscription.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Adresse Email</label>
                                <input type="email" class="form-control border-dark rounded-0" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">Mot de passe</label>
                                <input type="password" class="form-control border-dark rounded-0" id="password" name="password" required>
                            </div>
                            <div class="mb-4">
                                <label for="password_confirm" class="form-label fw-semibold">Confirmer le mot de passe</label>
                                <input type="password" class="form-control border-dark rounded-0" id="password_confirm" name="password_confirm" required>
                            </div>
                            <button type="submit" class="btn btn-dark w-100 rounded-0 fw-bold">S'INSCRIRE</button>
                            
                            <div class="mt-3 text-center">
                                <a href="index.php" class="text-dark" style="font-size: 0.85rem;">Déjà un compte ? Se connecter</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```[cite: 9]

Teste de créer un compte, puis essaie de recréer un compte avec exactement le même email pour valider le message d'erreur. 

<FollowUp label="Gérer l'interface Administrateur ?" query="Une fois l'inscription validée, veux-tu qu'on s'occupe de la séparation des rôles (Joueur vs Admin) pour valider le bloc 'Connecter un administrateur' de ta grille ?"/>