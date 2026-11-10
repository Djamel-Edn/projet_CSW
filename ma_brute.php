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

// 1. Vérification de l'existence de la brute
$sql_check = "SELECT * FROM brutes WHERE id_user = :id_user LIMIT 1";
$stmt_check = $pdo->prepare($sql_check);
$stmt_check->execute([':id_user' => $id_user]);
$brute = $stmt_check->fetch();

// 2. Création stricte avec les règles du cahier des charges
if (!$brute && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = trim($_POST['pseudo'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $avatar = $_POST['avatar'] ?? '🥷 Ninja';

    if (!empty($pseudo)) {
        $pv = 30;
        $force = rand(5, 8);
        $defense = 10 - $force;

        $sql_insert = "INSERT INTO brutes (id_user, pseudo, description, avatar, force_brute, defense, pv) 
                       VALUES (:id_user, :pseudo, :description, :avatar, :force, :defense, :pv)";
        $stmt_insert = $pdo->prepare($sql_insert);
        
        try {
            $stmt_insert->execute([
                ':id_user' => $id_user,
                ':pseudo' => $pseudo,
                ':description' => $description,
                ':avatar' => $avatar,
                ':force' => $force,
                ':defense' => $defense,
                ':pv' => $pv
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

// 3. Récupération du défi en cours (ouvert) pour la gestion
$sql_mes_defis = "SELECT * FROM defis WHERE id_auteur = :id_user AND etat = 'ouvert' ORDER BY id_defi DESC";
$stmt_mes_defis = $pdo->prepare($sql_mes_defis);
$stmt_mes_defis->execute([':id_user' => $id_user]);
$mes_defis = $stmt_mes_defis->fetchAll();

// 4. Récupération de l'historique des combats terminés (auteur OU candidat accepté)
$sql_historique = "SELECT d.id_defi, d.titre, d.id_vainqueur, 
                          b_auteur.pseudo AS auteur, 
                          b_adversaire.pseudo AS adversaire
                   FROM defis d
                   JOIN brutes b_auteur ON d.id_auteur = b_auteur.id_user
                   LEFT JOIN candidatures c ON d.id_defi = c.id_defi AND c.etat = 'acceptee'
                   LEFT JOIN brutes b_adversaire ON c.id_candidat = b_adversaire.id_user
                   WHERE d.etat = 'termine' 
                   AND (d.id_auteur = :id_user OR c.id_candidat = :id_user)
                   ORDER BY d.id_defi DESC";
$stmt_historique = $pdo->prepare($sql_historique);
$stmt_historique->execute([':id_user' => $id_user]);
$historique = $stmt_historique->fetchAll();
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
        <?php if ($brute): ?>
            <div class="row justify-content-center">
                
                <!-- COLONNE GAUCHE : STATISTIQUES -->
                <div class="col-lg-5 mb-4">
                    <div class="card border-dark rounded-0 shadow-sm h-100">
                        <div class="card-body p-4 text-center">
                            <h4 class="fw-bold mb-4 text-uppercase">Profil de ta Brute</h4>
                            <div class="display-1 mb-3"><?= htmlspecialchars($brute['avatar']) ?></div>
                            <h3 class="fw-bold"><?= htmlspecialchars($brute['pseudo']) ?></h3>
                            <p class="text-muted fst-italic">"<?= htmlspecialchars($brute['description']) ?>"</p>
                            <hr class="border-dark">
                            
                            <div class="mb-3 text-center">
                                <h4 class="fw-bold text-danger mb-0">❤️ <?= $brute['pv'] ?? 30 ?> / 30</h4>
                                <small class="text-muted text-uppercase fw-bold">Points de vie</small>
                            </div>
                            
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

                <!-- COLONNE DROITE : GESTION & HISTORIQUE -->
                <div class="col-lg-7">
                    
                    <!-- DÉFI EN COURS -->
                    <div class="card border-dark rounded-0 shadow-sm mb-4">
                        <div class="card-header bg-dark text-white rounded-0 fw-bold text-uppercase py-3">
                            ⚔️ Mon défi en cours
                        </div>
                        <div class="card-body p-4">
                            <?php if (empty($mes_defis)): ?>
                                <p class="text-muted mb-0">Tu n'as aucun défi ouvert dans l'Arène en ce moment.</p>
                            <?php else: ?>
                                <div class="list-group rounded-0">
                                    <?php foreach ($mes_defis as $d): ?>
                                        <a href="gerer_defi.php?id=<?= $d['id_defi'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-dark border-2 border-start-0 border-end-0 border-top-0 mb-2 pb-3">
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($d['titre']) ?></h6>
                                                <small class="text-success fw-bold text-uppercase">En attente d'adversaire</small>
                                            </div>
                                            <span class="btn btn-sm btn-outline-dark rounded-0 fw-bold">VOIR CANDIDATS</span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- HISTORIQUE DES COMBATS -->
                    <div class="card border-dark rounded-0 shadow-sm h-100">
                        <div class="card-header bg-secondary text-white rounded-0 fw-bold text-uppercase py-3">
                            📜 Historique des combats
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($historique)): ?>
                                <p class="text-muted p-4 mb-0">Ta brute n'a encore participé à aucun combat sanglant.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush rounded-0">
                                    <?php foreach ($historique as $h): ?>
                                        <?php 
                                            // Déterminer l'issue du combat pour l'utilisateur
                                            $victoire = ($h['id_vainqueur'] == $id_user);
                                            $badge_class = $victoire ? 'bg-success' : 'bg-danger';
                                            $badge_texte = $victoire ? 'VICTOIRE' : 'DÉFAITE';
                                            $adversaire = ($h['auteur'] == $brute['pseudo']) ? $h['adversaire'] : $h['auteur'];
                                        ?>
                                        <a href="voir_defi.php?id=<?= $h['id_defi'] ?>" class="list-group-item list-group-item-action border-bottom border-dark p-3">
                                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($h['titre']) ?></h6>
                                                <span class="badge <?= $badge_class ?> rounded-0 fs-6"><?= $badge_texte ?></span>
                                            </div>
                                            <small class="text-muted">VS <strong><?= htmlspecialchars($adversaire) ?></strong></small>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

        <?php else: ?>
            <!-- FORMULAIRE DE CRÉATION (Inchangé) -->
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card border-dark rounded-0 p-4 shadow-sm">
                        <h4 class="fw-bold mb-3 text-center">CRÉER TA BRUTE</h4>
                        
                        <?php if (!empty($erreur)): ?>
                            <div class="alert alert-danger rounded-0"><?= htmlspecialchars($erreur) ?></div>
                        <?php endif; ?>

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
                            <div class="alert alert-light border border-dark rounded-0 small fst-italic p-3">
                                <strong>Important :</strong> Tes 30 points de vie, ta force et ta défense seront tirés au sort et attribués définitivement par le serveur.
                            </div>
                            <button type="submit" class="btn btn-dark w-100 rounded-0 fw-bold">GÉNÉRER MA BRUTE</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>