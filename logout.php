<?php
// logout.php
session_start();

// Écraser les variables de session avec un tableau vide
$_SESSION = array();

// Détruire la session complètement
session_destroy();

// Rediriger l'utilisateur vers la page de connexion
header("Location: login.php");
exit();
?>
