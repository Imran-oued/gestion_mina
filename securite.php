<?php
// securite.php

// Forcer le cookie de session à expirer à la fermeture du navigateur
session_set_cookie_params(0);
session_start();

// Durée de vie maximale d'inactivité (ex: 30 minutes = 1800 secondes)
$timeout = 1800;

// Vérifier si la variable de session 'connecte' existe et vaut true
if (!isset($_SESSION['connecte']) || $_SESSION['connecte'] !== true) {
    header("Location: login.php");
    exit();
}

// Vérifier l'inactivité pour déconnecter automatiquement
if (isset($_SESSION['dernier_acces'])) {
    $duree_inactivite = time() - $_SESSION['dernier_acces'];
    if ($duree_inactivite > $timeout) {
        // Trop de temps d'inactivité, on détruit la session
        session_unset();
        session_destroy();
        header("Location: login.php?expire=1");
        exit();
    }
}
// Mettre à jour l'heure de la dernière activité
$_SESSION['dernier_acces'] = time();
?>
