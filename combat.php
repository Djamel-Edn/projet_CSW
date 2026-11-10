<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header('Location: index.php');
    exit();
}

require_once 'db.inc.php';

$id_user =$_SESSION['id_user'];
$id_defi =$_POST['id_defi'] ?? null;
$id_candidature =$_POST['id_candidature'] ?? null;

if (!$id_defi \vert{}\vert{} !$id_candidature) {
    header('Location: ma_brute.php');
    exit();
}

// 1. Vérification : le défi appartient-il à l'utilisateur et est-il bien ouvert ?
$sql_defi = "SELECT * FROM defis WHERE id_defi = :id_defi AND id_auteur = :id_auteur AND etat = 'ouvert' LIMIT 1";
$stmt_defi =$pdo->prepare($sql_defi);$stmt_defi->execute([':id_defi' => $id_defi, ':id_auteur' =>$id_user]);
$defi =$stmt_defi->fetch();

if (!$defi) {
    die("Erreur : Ce défi n'est plus valide ou ne vous appartient pas.");
}

// 2. Récupération de la brute de l'auteur (Joueur 1)
$sql_auteur = "SELECT * FROM brutes WHERE id_user = :id_user";
$stmt_auteur = $pdo->prepare($sql_auteur);
$stmt_auteur->execute([':id_user' =>$id_user]);
$brute_auteur =$stmt_auteur->fetch();

// 3. Récupération de la brute du candidat sélectionné (Joueur 2)
$sql_candidat = "SELECT b.*, c.id_candidat FROM candidatures c 
                 JOIN brutes b ON c.id_candidat = b.id_user 
                 WHERE c.id_candidature = :id_candidature AND c.id_defi = :id_defi";
$stmt_candidat =$pdo->prepare($sql_candidat);$stmt_candidat->execute([':id_candidature' => $id_candidature, ':id_defi' =>$id_defi]);
$brute_candidat =$stmt_candidat->fetch();

if (!$brute_auteur \vert{}\vert{} !$brute_candidat) {
    die("Erreur : Impossible de trouver les combattants.");
}

// 4. SIMULATION DU COMBAT (Aléatoire basé sur les statistiques)
$chance_auteur = rand(1, 10);$chance_candidat = rand(1, 10);

$score_auteur = ($brute_auteur['force_brute'] * 1.5) + ($brute_auteur['defense'] *$chance_auteur);
$score_candidat = ($brute_candidat['force_brute'] * 1.5) + ($brute_candidat['defense'] *$chance_candidat);

if ($score_auteur >=$score_candidat) {
    $id_vainqueur =$brute_auteur['id_user'];
    $id_perdant =$brute_candidat['id_user'];
    $nom_vainqueur =$brute_auteur['pseudo'];
    $nom_perdant =$brute_candidat['pseudo'];
} else {
    $id_vainqueur =$brute_candidat['id_user'];
    $id_perdant =$brute_auteur['id_user'];
    $nom_vainqueur =$brute_candidat['pseudo'];
    $nom_perdant =$brute_auteur['pseudo'];
}

// Génération du rapport de combat
$rapport = "Le combat fut d'une violence inouïe ! \n";
$rapport .= "{$brute_auteur['pseudo']} a frappé avec une intensité de " . round($score_auteur) . ".\n";
$rapport .= "{$brute_candidat['pseudo']} a riposté avec une intensité de " . round($score_candidat) . ".\n";
$rapport .= "Vainqueur final : **$nom_vainqueur** !";

// 5. MISES À JOUR DANS LA BASE DE DONNÉES (Transaction)
try {
    $pdo->beginTransaction();

    // A. Mettre à jour le défi (fermé, vainqueur et rapport)
    $sql_update_defi = "UPDATE defis SET etat = 'termine', id_vainqueur = :id_vainqueur, rapport_combat = :rapport WHERE id_defi = :id_defi";
    $stmt_update_defi = $pdo->prepare($sql_update_defi);
    $stmt_update_defi->execute([':id_vainqueur' =>$id_vainqueur, ':rapport' => $rapport, ':id_defi' =>$id_defi]);

    // B. Mettre à jour la candidature sélectionnée en 'acceptee'
    $sql_accept_candidature = "UPDATE candidatures SET etat = 'acceptee' WHERE id_candidature = :id_candidature";
    $stmt_accept_candidature = $pdo->prepare($sql_accept_candidature);
    $stmt_accept_candidature->execute([':id_candidature' =>$id_candidature]);

    // C. Mettre à jour les AUTRES candidatures de ce défi en 'refusee'
    $sql_reject_others = "UPDATE candidatures SET etat = 'refusee' WHERE id_defi = :id_defi AND id_candidature != :id_candidature";
    $stmt_reject_others =$pdo->prepare($sql_reject_others);$stmt_reject_others->execute([':id_defi' => $id_defi, ':id_candidature' =>$id_candidature]);

    // D. Ajouter une victoire au gagnant
    $sql_win = "UPDATE brutes SET victoires = victoires + 1 WHERE id_user = :id_vainqueur";
    $stmt_win = $pdo->prepare($sql_win);
    $stmt_win->execute([':id_vainqueur' =>$id_vainqueur]);

    // E. Ajouter une défaite au perdant
    $sql_lose = "UPDATE brutes SET defaites = defaites + 1 WHERE id_user = :id_perdant";
    $stmt_lose = $pdo->prepare($sql_lose);
    $stmt_lose->execute([':id_perdant' =>$id_perdant]);

    $pdo->commit();
} catch (Exception $e) {$pdo->rollBack();
    die("Erreur lors de l'enregistrement du