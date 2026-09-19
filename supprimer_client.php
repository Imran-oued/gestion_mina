<?php
require_once 'securite.php';
require_once 'db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        // La suppression du client entraînera la suppression en cascade de ses réservations, 
        // factures et paiements grâce aux contraintes ON DELETE CASCADE dans la base de données.
        $stmt = $pdo->prepare('DELETE FROM clients WHERE id = :id');
        $stmt->execute(['id' => $id]);
        
        // Redirection avec un message de succès (optionnel, on redirige juste vers l'index pour le moment)
        header('Location: index.php?msg=client_supprime');
        exit;
    } catch (PDOException $e) {
        die("Erreur lors de la suppression : " . $e->getMessage());
    }
} else {
    header('Location: index.php');
    exit;
}
