<?php
// supprimer_paiement.php
require_once 'securite.php';
require_once 'db.php';

$paiement_id = $_GET['id'] ?? null;

if ($paiement_id) {
    try {
        // On utilise une transaction pour être sûr que tout se fait correctement ensemble
        $pdo->beginTransaction();

        // Étape A : Récupérer le montant du paiement et l'ID de la facture associée
        $stmtSelect = $pdo->prepare("SELECT facture_id, montant FROM paiements WHERE id = :id");
        $stmtSelect->bindParam(':id', $paiement_id);
        $stmtSelect->execute();
        $paiement = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if ($paiement) {
            $facture_id = $paiement['facture_id'];
            $montant_a_annuler = $paiement['montant'];

            // Optionnel mais recommandé : on récupère la facture pour mettre à jour le statut correctement
            $stmtFact = $pdo->prepare("SELECT montant_total, montant_paye FROM factures WHERE id = :facture_id");
            $stmtFact->bindParam(':facture_id', $facture_id);
            $stmtFact->execute();
            $facture = $stmtFact->fetch(PDO::FETCH_ASSOC);

            if ($facture) {
                $nouveau_montant_paye = $facture['montant_paye'] - $montant_a_annuler;
                if ($nouveau_montant_paye < 0) { 
                    $nouveau_montant_paye = 0; // Sécurité pour éviter les nombres négatifs
                }

                // Déterminer le nouveau statut
                if ($nouveau_montant_paye == 0) {
                    $nouveau_statut = 'impayé';
                } elseif ($nouveau_montant_paye >= $facture['montant_total']) {
                    $nouveau_statut = 'payé';
                } else {
                    $nouveau_statut = 'partiel';
                }

                // Étape B : Mettre à jour la facture (soustraire le montant et changer le statut)
                $stmtUpdate = $pdo->prepare("UPDATE factures SET montant_paye = :nouveau_montant, statut_paiement = :nouveau_statut WHERE id = :facture_id");
                $stmtUpdate->bindParam(':nouveau_montant', $nouveau_montant_paye);
                $stmtUpdate->bindParam(':nouveau_statut', $nouveau_statut);
                $stmtUpdate->bindParam(':facture_id', $facture_id);
                $stmtUpdate->execute();
            }

            // Étape C : Supprimer le paiement de la table paiements
            $stmtDelete = $pdo->prepare("DELETE FROM paiements WHERE id = :id");
            $stmtDelete->bindParam(':id', $paiement_id);
            $stmtDelete->execute();

            // Valider la transaction
            $pdo->commit();
        } else {
            // Le paiement n'a pas été trouvé, on annule la transaction
            $pdo->rollBack();
        }

    } catch (PDOException $e) {
        $pdo->rollBack();
        // En cas d'erreur on pourrait afficher un message, mais ici on redirige simplement
    }
}

// Redirection automatique vers l'historique
header("Location: historique.php");
exit();
?>
