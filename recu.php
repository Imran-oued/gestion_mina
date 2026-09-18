<?php
// recu.php
require_once 'securite.php';
require_once 'db.php';

$paiement_id = $_GET['paiement_id'] ?? null;

if (!$paiement_id) {
    die("ID de paiement manquant.");
}

try {
    // Récupérer toutes les infos pour le reçu
    $stmt = $pdo->prepare("
        SELECT 
            p.id AS num_recu,
            p.montant,
            p.methode_paiement,
            f.montant_total,
            f.montant_paye,
            r.description,
            c.nom,
            c.prenom
        FROM paiements p
        JOIN factures f ON p.facture_id = f.id
        JOIN reservations r ON f.reservation_id = r.id
        JOIN clients c ON r.client_id = c.id
        WHERE p.id = :paiement_id
    ");
    $stmt->bindParam(':paiement_id', $paiement_id);
    $stmt->execute();
    $recu = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recu) {
        die("Reçu introuvable.");
    }

    $date_actuelle = date('d/m/Y à H:i');
    $reste = $recu['montant_total'] - $recu['montant_paye'];

} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reçu #<?= htmlspecialchars($recu['num_recu']) ?> - Mina Voyage</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            /* Style machine à écrire / ticket */
            background-color: #e9ecef;
            color: #000;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .receipt-container {
            background: #fff;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border-top: 10px solid #2c3e50;
            position: relative;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #ccc;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .receipt-header h1 {
            margin: 0;
            font-size: 28px;
            color: #2c3e50;
            letter-spacing: 2px;
        }

        .receipt-header p {
            margin: 5px 0 0;
            font-size: 14px;
            color: #555;
        }

        .receipt-details {
            margin-bottom: 20px;
        }

        .receipt-details p {
            margin: 8px 0;
            font-size: 15px;
            display: flex;
            justify-content: space-between;
        }

        .divider {
            border-bottom: 2px dashed #ccc;
            margin: 20px 0;
        }

        .amount-highlight {
            font-size: 20px;
            font-weight: bold;
            background: #f1f2f6;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            margin: 20px 0;
        }

        .footer-note {
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 30px;
        }

        .btn-print {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background 0.3s;
        }

        .btn-print:hover {
            background-color: #2980b9;
        }

        .btn-back {
            color: #34495e;
            text-decoration: none;
            margin-top: 20px;
            font-family: 'Segoe UI', sans-serif;
        }

        /* Masquer les boutons lors de l'impression */
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }

            .receipt-container {
                box-shadow: none;
                border-top: none;
                max-width: 100%;
                padding: 0;
            }

            .btn-print,
            .btn-back {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <button class="btn-print" onclick="window.print()">🖨️ Imprimer / Sauvegarder en PDF</button>

    <div class="receipt-container">
        <div class="receipt-header">
            <img src="LOGO.jpg" alt="Logo Mina Voyage" style="max-width: 180px; margin-bottom: 10px;">
            <p>Votre partenaire de confiance</p>
            <p>123 En face du Lycée mixte de Gounghin, Ouagadougou</p>
            <p>Tél : +226 50 50 58 50 / 76 46 46 15</p>
            <p> Whatsapp : 78 36 30 77</p>
        </div>

        <div class="receipt-details">
            <p><strong>REÇU N° :</strong>
                <span>#<?= htmlspecialchars(str_pad($recu['num_recu'], 5, '0', STR_PAD_LEFT)) ?></span>
            </p>
            <p><strong>DATE :</strong> <span><?= htmlspecialchars($date_actuelle) ?></span></p>
            <p><strong>CLIENT :</strong> <span><?= htmlspecialchars($recu['nom'] . ' ' . $recu['prenom']) ?></span></p>
        </div>

        <div class="divider"></div>

        <div class="receipt-details">
            <p><strong>DESCRIPTION :</strong> <span><?= htmlspecialchars($recu['description']) ?></span></p>
            <p><strong>MÉTHODE :</strong> <span><?= htmlspecialchars($recu['methode_paiement']) ?></span></p>
        </div>

        <div class="amount-highlight">
            MONTANT PAYÉ : <?= number_format($recu['montant'], 0, ',', ' ') ?> FCFA
        </div>

        <div class="divider"></div>

        <div class="receipt-details" style="font-size: 13px; color: #555;">
            <p>Total du dossier : <span><?= number_format($recu['montant_total'], 0, ',', ' ') ?> FCFA</span></p>
            <p>Total déjà réglé : <span><?= number_format($recu['montant_paye'], 0, ',', ' ') ?> FCFA</span></p>
            <p style="color: <?= $reste > 0 ? '#e74c3c' : '#2ecc71' ?>; font-weight: bold;">
                Reste à payer : <span><?= number_format(max(0, $reste), 0, ',', ' ') ?> FCFA</span>
            </p>
        </div>

        <div class="footer-note">
            Merci pour votre confiance ! <br>
            Le paiement fait foi d'acceptation de nos conditions générales.
        </div>
        <div style="margin-top: 50px; display: flex; justify-content: flex-end;">
            <div style="width: 250px; text-align: center;">
                <p style="font-weight: bold; color: #333; margin-bottom: 80px; font-size: 14px;">Cachet et Signature :
                </p>
                <p style="font-size: 12px; color: #777; border-top: 1px dotted #ccc; padding-top: 5px;">La Direction -
                    Mina Voyage</p>
            </div>
        </div>
    </div>

    <a href="dossiers.php" class="btn-back">← Retour aux dossiers</a>

</body>

</html>