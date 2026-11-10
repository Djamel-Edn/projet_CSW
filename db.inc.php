<?php
// db.inc.php

$host = 'localhost'; 
$dbname = 'pixel_brawlers';
$user = 'root'; 
$pass = 'root'; // Le mot de passe par défaut de UwAmp


// 2. VERSION DÉPLOIEMENT - À activer juste avant d'envoyer sur le serveur

// $host = 'localhost'; 
// $dbname = 'bdd_7_5'; 
// $user = 'grp_7_5'; 
// $pass = 'uqm4AM5gTVhEmv9f';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>