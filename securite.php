<?php
// securite.php
session_start();

// Vérifier si la variable de session 'connecte' existe et vaut true
if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    // Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
    header("Location: login.php");
    exit();
}
?>
