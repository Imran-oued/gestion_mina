<?php
// historique.php
require_once 'securite.php';
require_once 'db.php';

try {
    // Requête JOIN pour lier paiements, factures, reservations et clients
    $sql = "
        SELECT 
            p.id AS paiement_id,
            p.date_paiement,
            p.montant,
            p.methode_paiement,
            p.reference_paiement,
            c.nom,
            c.prenom,
            r.description
        FROM paiements p
        JOIN factures f ON p.facture_id = f.id
        JOIN reservations r ON f.reservation_id = r.id
        JOIN clients c ON r.client_id = c.id
        ORDER BY p.id DESC
    ";
    $stmt = $pdo->query($sql);
    $encaissements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $encaissements = [];
    $error = "Erreur de base de données : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mina Voyage - Historique des Encaissements</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background-color: #fff;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background-color: #2c3e50;
            color: #ffffff;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .empty-message {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
            font-style: italic;
        }
        .actions-header {
            margin-bottom: 20px;
        }
        .btn-back {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        .btn-back:hover {
            text-decoration: underline;
        }
        .amount {
            color: #2ecc71;
            font-weight: bold;
            font-size: 16px;
        }
        .badge-methode {
            background-color: #f1f2f6;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 13px;
            color: #2f3542;
            border: 1px solid #ced6e0;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="actions-header">
        <a href="index.php" class="btn-back">← Retour au tableau de bord</a>
    </div>
    
    <h1>💰 Historique des Encaissements</h1>
    
    <?php if (isset($error)): ?>
        <div style="background: #fab1a0; color: #d63031; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Client</th>
                <th>Description du Service</th>
                <th>Méthode</th>
                <th>Montant Encaissé</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_encaisse = 0;
            if (count($encaissements) > 0): 
            ?>
                <?php foreach ($encaissements as $enc): 
                    // Si la date_paiement est définie dans la BD, on la formate
                    $date_str = !empty($enc['date_paiement']) ? date('d/m/Y H:i', strtotime($enc['date_paiement'])) : '-';
                    $total_encaisse += $enc['montant'];
                ?>
                    <tr>
                        <td style="color: #7f8c8d; font-size: 14px;"><?= htmlspecialchars($date_str) ?></td>
                        <td><strong><?= htmlspecialchars($enc['nom'] . ' ' . $enc['prenom']) ?></strong></td>
                        <td><?= htmlspecialchars($enc['description']) ?></td>
                        <td>
                            <span class="badge-methode"><?= htmlspecialchars($enc['methode_paiement']) ?></span>
                            <?php if (!empty($enc['reference_paiement'])): ?>
                                <br><small style="color: #7f8c8d; font-size: 12px; margin-top: 5px; display: inline-block;">Réf: <?= htmlspecialchars($enc['reference_paiement']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="amount">+ <?= number_format($enc['montant'], 0, ',', ' ') ?> FCFA</td>
                        <td>
                            <a href="supprimer_paiement.php?id=<?= htmlspecialchars($enc['paiement_id']) ?>" 
                               onclick="return confirm('Voulez-vous vraiment annuler ce paiement ? Cette action remettra la facture en impayé.');" 
                               style="background-color: #e74c3c; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: bold; transition: 0.3s;">
                               🗑️ Annuler
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <!-- Ligne de total à la fin -->
                <tr style="background-color: #f1f8ff; border-top: 2px solid #2c3e50;">
                    <td colspan="4" style="text-align: right; font-weight: bold; font-size: 18px;">Total des encaissements :</td>
                    <td class="amount" style="font-size: 18px;"><?= number_format($total_encaisse, 0, ',', ' ') ?> FCFA</td>
                    <td></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="empty-message">Aucun encaissement trouvé.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>