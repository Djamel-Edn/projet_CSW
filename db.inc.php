<?php
// db.inc.php
$host = 'moduleweb.fr'; 
$dbname = 'bdd_7_5'; // Remplace par le nom de ta base locale
$user = 'grp_7_5'; // Souvent 'root' en local
$pass = 'uqm4AM5gTVhEmv9f'; // Souvent vide sur Windows (WAMP/XAMPP), ou 'root' sur Mac (MAMP)

try {
    // Création de la connexion PDO
    $pdo = new PDO("mysql:host=$host;port=3307;dbname=$dbname;charset=utf8mb4", $user, $pass);
    
    // Configuration des options PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>