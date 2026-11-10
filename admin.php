<?php
session_start();

// Protection stricte : Seul un administrateur peut accéder à cette page
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?erreur=acces_refuse');
    exit();
}

require_once 'db.inc.php';

$message = '';

// 1. TRAITEMENT DES ACTIONS ADMINISTRATEUR
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Action A : Activer / Désactiver un compte
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_compte') {
        $id_cible = (int)$_POST['id_utilisateur'];
        
        // Sécurité : l'admin ne peut pas se désactiver lui-même
        if ($id_cible !== $_SESSION['id_user']) {
            // Si actif=1, on passe à 0, sinon on passe à 1
            $sql = "UPDATE utilisateurs SET actif = IF(actif=1, 0, 1) WHERE id_user = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $id_cible]);
            $message = "Le statut du compte a été mis à jour avec succès.";
        } else {
            $message = "Vous ne pouvez pas désactiver votre propre compte administrateur.";
        }
    }
    
    // Action B : Supprimer un défi
    if (isset($_POST['action']) && $_POST['action'] === 'supprimer_defi') {
        $id_defi = (int)$_POST['id_defi'];
        
        // Pour éviter les erreurs de clés étrangères, on supprime d'abord les candidatures liées
        $pdo->prepare("DELETE FROM candidatures WHERE id_defi = :id")->execute([':id' => $id_defi]);
        
        // Ensuite on supprime le défi lui-même
        $pdo->prepare("DELETE FROM defis WHERE id_defi = :id")->execute([':id' => $id_defi]);
        $message = "Le défi et ses candidatures ont été définitivement supprimés.";
    }
}

// 2. RÉCUPÉRATION DES DONNÉES POUR L'AFFICHAGE
// Tous les utilisateurs (sauf l'admin courant pour plus de clarté)
$utilisateurs = $pdo->query("SELECT id_user, email, role, actif FROM utilisateurs WHERE id_user != " . $_SESSION['id_user'] . " ORDER BY id_user DESC")->fetchAll();

// Tous les défis
$defis = $pdo->query("
    SELECT d.id_defi, d.titre, d.etat, b.pseudo 
    FROM defis d 
    LEFT JOIN brutes b ON d.id_auteur = b.id_user 
    ORDER BY d.id_defi DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pixel Brawlers - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

   <!-- NAV UNIFIÉE SANS LE BOUTON MA BRUTE -->
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
    <div class="container mt-4">
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-warning border-dark rounded-0 fw-bold shadow-sm" role="alert">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- SECTION : GESTION DES UTILISATEURS -->
            <div class="col-lg-6">
                <div class="card border-dark rounded-0 shadow-sm h-100">
                    <div class="card-header bg-dark text-white rounded-0 fw-bold text-uppercase py-3">
                        👥 Gérer les Comptes
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Email</th>
                                    <th>Statut</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($utilisateurs as $u): ?>
                                    <tr>
                                        <td class="align-middle"><?= htmlspecialchars($u['email']) ?></td>
                                        <td class="align-middle">
                                            <?php if ($u['actif'] == 1): ?>
                                                <span class="badge bg-success rounded-0">Actif</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger rounded-0">Désactivé</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <form action="admin.php" method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_compte">
                                                <input type="hidden" name="id_utilisateur" value="<?= $u['id_user'] ?>">
                                                <?php if ($u['actif'] == 1): ?>
                                                    <button type="submit" class="btn btn-sm btn-danger fw-bold rounded-0">Bannir</button>
                                                <?php else: ?>
                                                    <button type="submit" class="btn btn-sm btn-success fw-bold rounded-0">Réactiver</button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if(empty($utilisateurs)): ?>
                                    <tr><td colspan="3" class="text-center text-muted py-3">Aucun autre joueur inscrit.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- SECTION : GESTION DES DÉFIS -->
            <div class="col-lg-6">
                <div class="card border-dark rounded-0 shadow-sm h-100">
                    <div class="card-header bg-dark text-white rounded-0 fw-bold text-uppercase py-3">
                        ⚔️ Gérer les Défis
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Titre</th>
                                    <th>Auteur</th>
                                    <th>État</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($defis as $d): ?>
                                    <tr>
                                        <td class="align-middle fw-bold"><?= htmlspecialchars($d['titre']) ?></td>
                                        <td class="align-middle"><?= htmlspecialchars($d['pseudo'] ?? 'Inconnu') ?></td>
                                        <td class="align-middle text-uppercase small fw-bold text-muted"><?= htmlspecialchars($d['etat']) ?></td>
                                        <td class="text-end">
                                            <form action="admin.php" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce défi définitivement ?');">
                                                <input type="hidden" name="action" value="supprimer_defi">
                                                <input type="hidden" name="id_defi" value="<?= $d['id_defi'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger fw-bold rounded-0">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if(empty($defis)): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">Aucun défi dans l'Arène.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>