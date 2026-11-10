<?php
session_start();

// Protection de la page : seul un joueur connecté peut créer un défi
if (!isset($_SESSION['id_user'])) {
    header('Location: index.php');
    exit();
}

require_once 'db.inc.php';

$erreur = '';
$succes = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!empty($titre) && !empty($description)) {
        // Insertion du défi dans la base de données (l'état 'ouvert' est mis par défaut)
        $sql = "INSERT INTO defis (id_auteur, titre, description) VALUES (:id_auteur, :titre, :description)";
        $stmt = $pdo->prepare($sql);
        
        try {
            $stmt->execute([
                ':id_auteur' => $_SESSION['id_user'],
                ':titre' => $titre,
                ':description' => $description
            ]);
            $succes = "Ton défi a été lancé avec succès ! Il est maintenant visible dans l'Arène.";
        } catch (PDOException $e) {
            $erreur = "Erreur lors de la création du défi : " . $e->getMessage();
        }
    } else {
        $erreur = "Veuillez remplir tous les champs du défi.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Brawlers - Lancer un Défi</title>
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

    <!-- CONTENU PRINCIPAL -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card border-dark rounded-0 shadow-sm p-4">
                    <h4 class="fw-bold mb-3 text-center text-uppercase">Lancer un Défi</h4>
                    <p class="text-muted text-center mb-4">Provoque les autres joueurs en duel. S'ils acceptent, tu pourras choisir ton adversaire.</p>

                    <?php if (!empty($erreur)): ?>
                        <div class="alert alert-danger rounded-0" role="alert">
                            <?= htmlspecialchars($erreur) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($succes)): ?>
                        <div class="alert alert-success rounded-0 text-center" role="alert">
                            <p class="mb-2"><?= htmlspecialchars($succes) ?></p>
                            <a href="index.php" class="btn btn-success rounded-0 fw-bold btn-sm mt-2">Voir l'Arène</a>
                            <a href="ma_brute.php" class="btn btn-outline-dark rounded-0 fw-bold btn-sm mt-2">Retour au profil</a>
                        </div>
                    <?php else: ?>
                        <form action="creer_defi.php" method="POST">
                            <div class="mb-3">
                                <label for="titre" class="form-label fw-semibold">Titre du défi</label>
                                <input type="text" class="form-control border-dark rounded-0" id="titre" name="titre" placeholder="Ex: Qui osera m'affronter ?" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="description" class="form-label fw-semibold">Conditions ou provocation (Description)</label>
                                <textarea class="form-control border-dark rounded-0" id="description" name="description" rows="4" placeholder="Ex: Je n'accepte que les guerriers avec au moins 5 victoires !" required></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-dark rounded-0 fw-bold text-uppercase">Publier le défi</button>
                                <a href="ma_brute.php" class="btn btn-outline-secondary rounded-0 fw-bold text-uppercase">Annuler</a>
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