<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: index.php');
    exit();
}

require_once 'db.inc.php';

$id_user = $_SESSION['id_user'];
$erreur = '';
$succes = '';

// 1. On vérifie si l'utilisateur possède déjà une brute
$sql_check = "SELECT * FROM brutes WHERE id_user = :id_user LIMIT 1";
$stmt_check = $pdo->prepare($sql_check);
$stmt_check->execute([':id_user' => $id_user]);
$brute = $stmt_check->fetch();

// 2. Traitement du formulaire de création
if (!$brute && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = trim($_POST['pseudo'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $avatar = $_POST['avatar'] ?? '🥷 Ninja';

    if (!empty($pseudo)) {
        $sql_insert = "INSERT INTO brutes (id_user, pseudo, description, avatar) VALUES (:id_user, :pseudo, :description, :avatar)";
        $stmt_insert = $pdo->prepare($sql_insert);
        
        try {
            $stmt_insert->execute([
                ':id_user' => $id_user,
                ':pseudo' => $pseudo,
                ':description' => $description,
                ':avatar' => $avatar
            ]);
            header('Location: ma_brute.php');
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $erreur = "Ce pseudo est déjà pris par un autre joueur.";
            } else {
                $erreur = "Erreur lors de la création : " . $e->getMessage();
            }
        }
    } else {
        $erreur = "Le pseudo est obligatoire.";
    }
}

// 3. Récupération des défis lancés par l'utilisateur
$sql_mes_defis = "SELECT * FROM defis WHERE id_auteur = :id_user ORDER BY id_defi DESC";
$stmt_mes_defis = $pdo->prepare($sql_mes_defis);
$stmt_mes_defis->execute([':id_user' => $id_user]);
$mes_defis = $stmt_mes_defis->fetchAll();
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
    <div class="container mt-5 mb-5">
        <?php if ($brute): ?>
            <div class="row justify-content-center">
                
                <!-- COLONNE GAUCHE : PROFIL DE LA BRUTE -->
                <div class="col-lg-5 mb-4">
                    <div class="card border-dark rounded-0 shadow-sm h-100">
                        <div class="card-body p-4 text-center">
                            <h4 class="fw-bold mb-4 text-uppercase">Profil de ta Brute</h4>
                            <div class="display-1 mb-3"><?= htmlspecialchars($brute['avatar']) ?></div>
                            <h3 class="fw-bold"><?= htmlspecialchars($brute['pseudo']) ?></h3>
                            <p class="text-muted fst-italic">"<?= htmlspecialchars($brute['description']) ?>"</p>
                            <hr class="border-dark">
                            <div class="row mt-4">
                                <div class="col-6 mb-3"><h5 class="fw-bold mb-0">⚔️ <?= $brute['force_brute'] ?></h5><small class="text-muted">FORCE</small></div>
                                <div class="col-6 mb-3"><h5 class="fw-bold mb-0">🛡️ <?= $brute['defense'] ?></h5><small class="text-muted">DÉFENSE</small></div>
                                <div class="col-6 mb-3"><h5 class="fw-bold mb-0 text-success">🏆 <?= $brute['victoires'] ?></h5><small class="text-muted">VICTOIRES</small></div>
                                <div class="col-6 mb-3"><h5 class="fw-bold mb-0 text-danger">💀 <?= $brute['defaites'] ?></h5><small class="text-muted">DÉFAITES</small></div>
                            </div>
                            <div class="mt-4">
                                <a href="creer_defi.php" class="btn btn-dark fw-bold rounded-0 w-100 text-uppercase">Lancer un défi</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COLONNE DROITE : GESTION DES DÉFIS -->
                <div class="col-lg-7">
                    <div class="card border-dark rounded-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-4 text-uppercase">Mes défis lancés</h4>
                            
                            <?php if (empty($mes_defis)): ?>
                                <p class="text-muted">Tu n'as lancé aucun défi pour le moment.</p>
                            <?php else: ?>
                                <div class="list-group rounded-0">
                                    <?php foreach ($mes_defis as $d): ?>
                                        <a href="gerer_defi.php?id=<?= $d['id_defi'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-dark mb-2">
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($d['titre']) ?></h6>
                                                <small class="<?= $d['etat'] === 'ouvert' ? 'text-success' : 'text-muted' ?> fw-bold text-uppercase">
                                                    État : <?= $d['etat'] ?>
                                                </small>
                                            </div>
                                            <span class="btn btn-sm btn-outline-dark rounded-0 fw-bold">GÉRER & CANDIDATS</span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        <?php else: ?>
            <!-- Formulaire de création si la brute n'existe pas (inchangé) -->
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card border-dark rounded-0 p-4">
                        <h4 class="fw-bold mb-3 text-center">CRÉER TA BRUTE</h4>
                        <form action="ma_brute.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pseudo</label>
                                <input type="text" class="form-control border-dark rounded-0" name="pseudo" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Avatar</label>
                                <select class="form-select border-dark rounded-0" name="avatar">
                                    <option>🥷 Ninja</option><option>🧙‍♂️ Sorcier</option><option>🧟‍♂️ Zombie</option><option>🤖 Cyborg</option><option>🧛‍♂️ Vampire</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea class="form-control border-dark rounded-0" name="description" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-dark w-100 rounded-0 fw-bold">GÉNÉRER MA BRUTE</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>