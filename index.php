<?php
// index.php
require_once 'securite.php';
require_once 'db.php';

// Fetch clients from the database
try {
    // Check if the clients table exists (assuming it was created in step 1, but we fetch anyway)
    $stmt = $pdo->query('SELECT * FROM clients ORDER BY id DESC');
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If the table might not exist yet, we catch the error to prevent ugly output
    $clients = [];
    $error = "Erreur de base de données (Avez-vous créé la table 'clients' ?) : " . $e->getMessage();
}

// Fetch stats for dashboard
try {
    $stmtStats = $pdo->query("
        SELECT 
            SUM(CASE WHEN LOWER(description) LIKE '%billet%' THEN 1 ELSE 0 END) as billets,
            SUM(CASE WHEN LOWER(description) LIKE '%visa%' THEN 1 ELSE 0 END) as visas,
            SUM(CASE WHEN LOWER(description) LIKE '%oumra%' THEN 1 ELSE 0 END) as oumra,
            SUM(CASE WHEN LOWER(description) LIKE '%hadj%' THEN 1 ELSE 0 END) as hadj
        FROM reservations
    ");
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats = ['billets' => 0, 'visas' => 0, 'oumra' => 0, 'hadj' => 0];
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mina Voyage - Gestion des Clients</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 15px;
        }

        .btn-add {
            display: inline-block;
            background-color: #3498db;
            color: #fff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }

        .btn-add:hover {
            background-color: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: #fff;
        }

        th,
        td {
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

        .error-message {
            background-color: #ffeaa7;
            color: #d63031;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>✈️ Mina Voyage - Tableau de Bord</h1>

        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Récapitulatif / Dashboard Stats -->
        <div style="display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap;">
            <div style="flex: 1; background: #3498db; color: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); min-width: 150px;">
                <div style="font-size: 28px; font-weight: bold;"><?= $stats['billets'] ?: 0 ?></div>
                <div style="font-size: 14px; text-transform: uppercase; margin-top: 5px; font-weight: 600;">✈️ Billets</div>
            </div>
            <div style="flex: 1; background: #f39c12; color: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); min-width: 150px;">
                <div style="font-size: 28px; font-weight: bold;"><?= $stats['visas'] ?: 0 ?></div>
                <div style="font-size: 14px; text-transform: uppercase; margin-top: 5px; font-weight: 600;">🛂 Visas</div>
            </div>
            <div style="flex: 1; background: #2ecc71; color: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); min-width: 150px;">
                <div style="font-size: 28px; font-weight: bold;"><?= $stats['oumra'] ?: 0 ?></div>
                <div style="font-size: 14px; text-transform: uppercase; margin-top: 5px; font-weight: 600;">🕋 Oumra</div>
            </div>
            <div style="flex: 1; background: #9b59b6; color: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); min-width: 150px;">
                <div style="font-size: 28px; font-weight: bold;"><?= $stats['hadj'] ?: 0 ?></div>
                <div style="font-size: 14px; text-transform: uppercase; margin-top: 5px; font-weight: 600;">🕋 Hadj</div>
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
            <a href="ajouter_client.php"
                style="padding: 10px 15px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px;">+
                Ajouter un nouveau client</a>
            <a href="nouvelle_reservation.php"
                style="padding: 10px 15px; background-color: #f39c12; color: white; text-decoration: none; border-radius: 5px;">📋
                Créer un dossier</a>
            <a href="dossiers.php"
                style="padding: 10px 15px; background-color: #8e44ad; color: white; text-decoration: none; border-radius: 5px;">📁
                Voir les dossiers</a>
            <a href="historique.php"
                style="padding: 10px 15px; background-color: #27ae60; color: white; text-decoration: none; border-radius: 5px;">💰
                Historique des encaissements</a>
            <a href="logout.php"
                style="padding: 10px 15px; background-color: #e74c3c; color: white; text-decoration: none; border-radius: 5px;">🔒
                Déconnexion</a>
        </div>



        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Téléphone</th>
                    <th>N° Pièce</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($clients) > 0): ?>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?= htmlspecialchars($client['id'] ?? '') ?></td>
                            <td><strong><?= htmlspecialchars($client['nom'] ?? '') ?></strong></td>
                            <td><?= htmlspecialchars($client['prenom'] ?? '') ?></td>
                            <td><?= htmlspecialchars($client['telephone'] ?? '') ?></td>
                            <td><?= htmlspecialchars($client['numero_piece'] ?: '-') ?></td>
                            <td>
                                <a href="supprimer_client.php?id=<?= $client['id'] ?>" 
                                   style="color: #e74c3c; text-decoration: none; font-weight: bold;" 
                                   onclick="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer ce client ?\n\nCela supprimera TOUTES ses réservations, factures et paiements de l\'historique. Cette action est irréversible.');">
                                   🗑️ Supprimer
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty-message">Aucun client dans la base de données. Cliquez sur le bouton
                            au-dessus pour en ajouter un.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>

</html>