<?php
// dossiers.php
require_once 'securite.php';
require_once 'db.php';

// Récupération des filtres depuis $_GET
$filtre_service = isset($_GET['service']) ? trim($_GET['service']) : 'tous';
$filtre_statut = isset($_GET['statut']) ? trim($_GET['statut']) : 'tous';
$search_piece = isset($_GET['search_piece']) ? trim($_GET['search_piece']) : '';

try {
    // Requête de base
    $sql = "
        SELECT 
            r.id AS reservation_id, 
            r.description, 
            c.nom, 
            c.prenom, 
            c.numero_piece,
            r.fichier_joint,
            f.id AS facture_id, 
            f.montant_total, 
            f.montant_paye, 
            f.statut_paiement
        FROM reservations r
        JOIN clients c ON r.client_id = c.id
        JOIN factures f ON r.id = f.reservation_id
        WHERE 1=1
    ";

    $params = [];

    // Recherche par N° de Pièce (CNIB / Passeport)
    if ($search_piece !== '') {
        $sql .= " AND c.numero_piece LIKE :search_piece";
        $params[':search_piece'] = '%' . $search_piece . '%';
    } else {
        // Filtre dynamique par service
        if ($filtre_service !== 'tous' && $filtre_service !== '') {
            $sql .= " AND r.description LIKE :service";
            $params[':service'] = '%' . $filtre_service . '%';
        }

        // Filtre dynamique par statut de paiement
        if ($filtre_statut === 'paye') {
            $sql .= " AND f.montant_paye >= f.montant_total";
        } elseif ($filtre_statut === 'impaye') {
            $sql .= " AND f.montant_paye < f.montant_total";
        }
    }

    $sql .= " ORDER BY r.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $dossiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $dossiers = [];
    $error = "Erreur de base de données : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mina Voyage - Tous les dossiers</title>
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

        /* Styles pour le formulaire de filtrage */
        .filter-form {
            display: flex;
            gap: 15px;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            flex: 1;
            min-width: 150px;
        }

        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }

        .form-group select,
        .form-group input {
            padding: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            font-size: 14px;
            outline: none;
        }

        .form-group select:focus,
        .form-group input:focus {
            border-color: #3498db;
        }

        .btn-filter {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            transition: background-color 0.3s;
            height: 40px;
            /* Aligné avec les inputs */
        }

        .btn-filter:hover {
            background-color: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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

        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            color: white;
            display: inline-block;
        }

        .badge-impaye {
            background-color: #e74c3c;
        }

        .badge-partiel {
            background-color: #f39c12;
        }

        .badge-paye {
            background-color: #2ecc71;
        }

        .btn-pay {
            display: inline-block;
            background-color: #27ae60;
            color: #fff;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-pay:hover {
            background-color: #219a52;
        }

        .btn-pay.disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
            pointer-events: none;
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
    </style>
</head>

<body>

    <div class="container">
        <div class="actions-header">
            <a href="index.php" class="btn-back">← Retour au tableau de bord</a>
        </div>

        <h1>📁 Liste des Dossiers (Réservations)</h1>

        <?php if (isset($error)): ?>
            <div style="background: #fab1a0; color: #d63031; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de filtrage -->
        <form method="GET" action="dossiers.php" class="filter-form">
            <div class="form-group">
                <label for="search_piece">N° Passeport ou CNIB :</label>
                <input type="text" name="search_piece" id="search_piece" placeholder="Rechercher par N° de pièce..."
                    value="<?= htmlspecialchars($search_piece) ?>">
            </div>

            <div class="form-group">
                <label for="service">Service :</label>
                <select name="service" id="service">
                    <option value="tous" <?= $filtre_service === 'tous' ? 'selected' : '' ?>>Tous les services</option>
                    <option value="billet" <?= $filtre_service === 'billet' ? 'selected' : '' ?>>Billet d'avion (Général)
                    </option>
                    <option value="ouaga paris" <?= $filtre_service === 'ouaga paris' ? 'selected' : '' ?>>Billet Ouaga -
                        Paris</option>
                    <option value="hadj" <?= $filtre_service === 'hadj' ? 'selected' : '' ?>>Hadj</option>
                    <option value="oumra" <?= $filtre_service === 'oumra' ? 'selected' : '' ?>>Oumra</option>
                    <option value="visa" <?= $filtre_service === 'visa' ? 'selected' : '' ?>>Visa</option>
                    <option value="assurance" <?= $filtre_service === 'assurance' ? 'selected' : '' ?>>Assurance Voyage
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="statut">Statut de paiement :</label>
                <select name="statut" id="statut">
                    <option value="tous" <?= $filtre_statut === 'tous' ? 'selected' : '' ?>>Tous les statuts</option>
                    <option value="paye" <?= $filtre_statut === 'paye' ? 'selected' : '' ?>>Totalement payé</option>
                    <option value="impaye" <?= $filtre_statut === 'impaye' ? 'selected' : '' ?>>Impayé / Partiel</option>
                </select>
            </div>

            <div class="form-group" style="flex: 0; min-width: auto;">
                <button type="submit" class="btn-filter">🔍 Filtrer</button>
            </div>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Réf. Dossier</th>
                    <th>Client</th>
                    <th>N° Pièce</th>
                    <th>Pièce jointe</th>
                    <th>Description</th>
                    <th>Montant Total</th>
                    <th>Reste à Payer</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($dossiers) > 0): ?>
                    <?php foreach ($dossiers as $d):
                        $reste = $d['montant_total'] - $d['montant_paye'];

                        // Déterminer la couleur du badge
                        $badgeClass = 'badge-impaye';
                        $statutTexte = 'Impayé';
                        if ($reste <= 0) {
                            $badgeClass = 'badge-paye';
                            $statutTexte = 'Soldé';
                        } elseif ($d['montant_paye'] > 0) {
                            $badgeClass = 'badge-partiel';
                            $statutTexte = 'Partiel';
                        }
                        ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars($d['reservation_id']) ?></strong></td>
                            <td><strong><?= htmlspecialchars($d['nom'] . ' ' . $d['prenom']) ?></strong></td>
                            <td><span style="color: #7f8c8d; font-size: 13px;"><?= htmlspecialchars($d['numero_piece'] ?: '-') ?></span></td>
                            <td>
                                <?php if (!empty($d['fichier_joint'])): ?>
                                    <a href="<?= htmlspecialchars($d['fichier_joint']) ?>" target="_blank" style="color: #3498db; text-decoration: none; font-size: 13px;">📄 Voir</a>
                                <?php else: ?>
                                    <span style="color: #bdc3c7;">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($d['description']) ?></td>
                            <td><strong><?= number_format($d['montant_total'], 0, ',', ' ') ?> FCFA</strong></td>
                            <td style="color: <?= $reste > 0 ? '#e74c3c' : '#2ecc71' ?>; font-weight: bold;">
                                <?= number_format(max(0, $reste), 0, ',', ' ') ?> FCFA
                            </td>
                            <td><span class="badge <?= $badgeClass ?>"><?= $statutTexte ?></span></td>
                            <td>
                                <?php if ($reste > 0): ?>
                                    <a href="payer.php?facture_id=<?= $d['facture_id'] ?>" class="btn-pay">💸 Payer</a>
                                <?php else: ?>
                                    <span class="btn-pay disabled">✔️ Réglé</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="empty-message">Aucun dossier trouvé avec ces critères de filtrage.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>

</html>