<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: index.php?erreur=acces_refuse');
    exit();
}

require_once 'db.inc.php';

$id_user = $_SESSION['id_user'];
$erreur = '';
$succes = '';
$peut_creer = true;

// 1. VÉRIFICATION STRICTE : Le joueur a-t-il déjà un défi actif ?
$sql_check_actif = "SELECT id_defi FROM defis WHERE id_auteur = :id_user AND etat = 'ouvert' LIMIT 1";
$stmt_check = $pdo->prepare($sql_check_actif);
$stmt_check->execute([':id_user' => $id_user]);
$defi_actif = $stmt_check->fetch();

if ($defi_actif) {
    $peut_creer = false;
    $erreur = "Tu as déjà un défi en cours dans l'Arène ! Tu dois attendre qu'un adversaire y réponde ou demander à un administrateur de le supprimer pour en lancer un nouveau.";
}

// 2. Traitement du formulaire (uniquement si autorisé)
if ($peut_creer && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!empty($titre) && !empty($description)) {
        $sql = "INSERT INTO defis (id_auteur, titre, description, etat) VALUES (:id_auteur, :titre, :description, 'ouvert')";
        $stmt = $pdo->prepare($sql);
        
        try {
            $stmt->execute([
                ':id_auteur' => $id_user,
                ':titre' => $titre,
                ':description' => $description
            ]);
            $succes = "Ton défi a été lancé avec succès ! Il est maintenant visible dans l'Arène.";
            $peut_creer = false; // On masque le formulaire après la réussite
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

    <!-- NAV UNIFIÉE -->
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
                    <a href="ma_brute.php" class="btn btn-dark btn-sm fw-bold rounded-0 me-2">MA BRUTE</a>
                    <a href="deconnexion.php" class="btn btn-outline-danger btn-sm fw-bold rounded-0">DÉCONNEXION</a>
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
                    
                    <?php if (!$peut_creer && empty($succes)): ?>
                        <p class="text-muted text-center mb-4">L'Arène n'autorise qu'un seul duel à la fois.</p>
                    <?php else: ?>
                        <p class="text-muted text-center mb-4">Provoque les autres joueurs en duel. S'ils acceptent, tu pourras choisir ton adversaire.</p>
                    <?php endif; ?>

                    <?php if (!empty($erreur)): ?>
                        <div class="alert alert-danger border-dark rounded-0 shadow-sm text-center" role="alert">
                            <strong>Halte !</strong> <?= htmlspecialchars($erreur) ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="ma_brute.php" class="btn btn-dark rounded-0 fw-bold">RETOUR AU PROFIL</a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($succes)): ?>
                        <div class="alert alert-success border-dark rounded-0 text-center shadow-sm" role="alert">
                            <p class="mb-2 fw-bold"><?= htmlspecialchars($succes) ?></p>
                            <div class="mt-3">
                                <a href="arene.php" class="btn btn-dark rounded-0 fw-bold btn-sm me-2">VOIR L'ARÈNE</a>
                                <a href="ma_brute.php" class="btn btn-outline-dark rounded-0 fw-bold btn-sm">MON PROFIL</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Affichage du formulaire uniquement si le joueur y est autorisé -->
                    <?php if ($peut_creer): ?>
                        <form action="creer_defi.php" method="POST">
                            <div class="mb-3">
                                <label for="titre" class="form-label fw-semibold">Titre du défi</label>
                                <input type="text" class="form-control border-dark rounded-0" id="titre" name="titre" placeholder="Ex: Qui osera m'affronter ?" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="description" class="form-label fw-semibold">Conditions ou provocation</label>
                                <textarea class="form-control border-dark rounded-0" id="description" name="description" rows="4" placeholder="Ex: Je n'accepte que les guerriers avec au moins 5 victoires !" required></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger rounded-0 fw-bold text-uppercase shadow-sm">Publier le défi</button>
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