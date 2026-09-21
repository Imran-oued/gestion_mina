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
    <!-- Add Google Fonts for modern typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            
            --color-billets: linear-gradient(135deg, #3b82f6, #2563eb);
            --color-visas: linear-gradient(135deg, #f59e0b, #d97706);
            --color-oumra: linear-gradient(135deg, #10b981, #059669);
            --color-hadj: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            padding: 40px 20px;
            line-height: 1.5;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: var(--card-bg);
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
        }

        .header-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid var(--border-color);
            gap: 25px;
        }

        .logo {
            max-height: 150px;
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        
        .logo:hover {
            transform: scale(1.05);
        }

        h1 {
            color: var(--text-main);
            margin: 0;
            font-size: 2.4rem;
            font-weight: 800;
            letter-spacing: -0.025em;
        }

        .error-message {
            background-color: #fef2f2;
            color: #dc2626;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            border: 1px solid #fca5a5;
            font-weight: 500;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .stat-card {
            color: white;
            padding: 28px 24px;
            border-radius: 16px;
            text-align: center;
            box-shadow: var(--shadow-md);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            display: block;
        }
        
        .stat-card::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 100%);
            pointer-events: none;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card.billets { background: var(--color-billets); }
        .stat-card.visas { background: var(--color-visas); }
        .stat-card.oumra { background: var(--color-oumra); }
        .stat-card.hadj { background: var(--color-hadj); }

        .stat-value {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-label {
            font-size: 0.9rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.05em;
            opacity: 0.95;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* Actions Grid */
        .actions-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 35px;
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            filter: brightness(110%);
        }

        .btn-add-client { background-color: #3b82f6; color: white; }
        .btn-create-folder { background-color: #f59e0b; color: white; }
        .btn-view-folders { background-color: #8b5cf6; color: white; }
        .btn-history { background-color: #10b981; color: white; }
        .btn-logout { background-color: #ef4444; color: white; margin-left: auto; }

        /* Table */
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #f8fafc;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 16px 20px;
            text-align: left;
            border-bottom: 2px solid var(--border-color);
        }

        td {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
            vertical-align: middle;
            font-size: 0.95rem;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: #f1f5f9;
        }
        
        td strong {
            font-weight: 600;
            color: #0f172a;
        }

        .btn-delete {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #ef4444;
            background: #fef2f2;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            border: 1px solid #fee2e2;
        }

        .btn-delete:hover {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fca5a5;
        }

        .empty-message {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
            font-style: italic;
        }

        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                text-align: center;
            }
            .btn-logout {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header-container">
            <img src="LOGO.jpg" alt="Mina Voyage Logo" class="logo">
            <h1>Mina Voyage - Tableau de Bord</h1>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Récapitulatif / Dashboard Stats -->
        <div class="stats-grid">
            <div class="stat-card billets">
                <div class="stat-value"><?= $stats['billets'] ?: 0 ?></div>
                <div class="stat-label">✈️ Billets</div>
            </div>
            <div class="stat-card visas">
                <div class="stat-value"><?= $stats['visas'] ?: 0 ?></div>
                <div class="stat-label">🛂 Visas</div>
            </div>
            <div class="stat-card oumra">
                <div class="stat-value"><?= $stats['oumra'] ?: 0 ?></div>
                <div class="stat-label">🕋 Oumra</div>
            </div>
            <div class="stat-card hadj">
                <div class="stat-value"><?= $stats['hadj'] ?: 0 ?></div>
                <div class="stat-label">🕋 Hadj</div>
            </div>
        </div>

        <div class="actions-grid">
            <a href="ajouter_client.php" class="action-btn btn-add-client">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Ajouter un nouveau client
            </a>
            <a href="nouvelle_reservation.php" class="action-btn btn-create-folder">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Créer un dossier
            </a>
            <a href="dossiers.php" class="action-btn btn-view-folders">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                Voir les dossiers
            </a>
            <a href="historique.php" class="action-btn btn-history">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Historique des encaissements
            </a>
            <a href="logout.php" class="action-btn btn-logout">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Déconnexion
            </a>
        </div>

        <div class="table-container">
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
                                    <a href="supprimer_client.php?id=<?= $client['id'] ?>" class="btn-delete"
                                       onclick="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer ce client ?\n\nCela supprimera TOUTES ses réservations, factures et paiements de l\'historique. Cette action est irréversible.');">
                                       <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                       Supprimer
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-message">Aucun client dans la base de données. Cliquez sur le bouton "Ajouter un nouveau client" pour en ajouter un.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>