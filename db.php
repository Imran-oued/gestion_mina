<?php
$host = 'localhost';
$dbname = 'minavoyage_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Définir le mode d'erreur PDO sur exception pour une meilleure gestion des erreurs
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Optionnel : décommentez la ligne ci-dessous pour tester si la connexion fonctionne
    // echo "Connexion réussie à la base de données.";
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>