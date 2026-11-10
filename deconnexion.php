<?php
session_start();
session_unset(); // Vider les variables de session
session_destroy(); // Déstruction de la session
header('Location: index.php'); // Redirect vers l'accueil
exit();
?>