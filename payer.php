<?php
// payer.php
require_once 'securite.php';
require_once 'db.php';

$facture_id = $_GET['facture_id'] ?? null;
$message = '';
$messageType = '';

if (!$facture_id) {
    die("ID de facture manquant dans l'URL.");
}

// Récupérer les informations de la facture et du dossier
try {
    $stmtFact = $pdo->prepare("
        SELECT f.*, r.description, c.nom, c.prenom
        FROM factures f
        JOIN reservations r ON f.reservation_id = r.id
        JOIN clients c ON r.client_id = c.id
        WHERE f.id = :facture_id
    ");
    $stmtFact->bindParam(':facture_id', $facture_id);
    $stmtFact->execute();
    $facture = $stmtFact->fetch(PDO::FETCH_ASSOC);

    if (!$facture) {
        die("Facture introuvable.");
    }
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}

$reste_a_payer = $facture['montant_total'] - $facture['montant_paye'];

// Traitement du formulaire de paiement
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $montant = floatval($_POST['montant'] ?? 0);
    $methode = $_POST['methode'] ?? '';
    $reference = $_POST['reference'] ?? '';

    if ($montant > 0 && $montant <= $reste_a_payer && !empty($methode)) {
        try {
            $pdo->beginTransaction();

            // 1. Insertion dans la table paiements
            // Si la colonne date_paiement n'existe pas par défaut avec CURRENT_TIMESTAMP, 
            // on peut omettre ou l'ajouter. On part du principe qu'elle a une valeur par défaut.
            $sqlPaiement = "INSERT INTO paiements (facture_id, montant, methode_paiement, reference_paiement) VALUES (:facture_id, :montant, :methode, :reference)";
            $stmtP = $pdo->prepare($sqlPaiement);
            $stmtP->bindParam(':facture_id', $facture_id);
            $stmtP->bindParam(':montant', $montant);
            $stmtP->bindParam(':methode', $methode);
            $stmtP->bindParam(':reference', $reference);
            $stmtP->execute();

            $paiement_id = $pdo->lastInsertId();

            // 2. Mise à jour de la table factures
            $nouveau_montant_paye = $facture['montant_paye'] + $montant;
            $nouveau_statut = ($nouveau_montant_paye >= $facture['montant_total']) ? 'payé' : 'partiel';

            $sqlUpdateF = "UPDATE factures SET montant_paye = :montant_paye, statut_paiement = :statut WHERE id = :facture_id";
            $stmtU = $pdo->prepare($sqlUpdateF);
            $stmtU->bindParam(':montant_paye', $nouveau_montant_paye);
            $stmtU->bindParam(':statut', $nouveau_statut);
            $stmtU->bindParam(':facture_id', $facture_id);
            $stmtU->execute();

            $pdo->commit();

            // Redirection vers le reçu
            header("Location: recu.php?paiement_id=" . $paiement_id);
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Erreur lors de l'enregistrement du paiement : " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = "Veuillez saisir un montant valide (inférieur ou égal au reste à payer) et une méthode.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Paiement - Mina Voyage</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .form-container {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 500px;
        }

        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
            margin-top: 0;
        }

        .recap-box {
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
        }

        .recap-box p {
            margin: 5px 0;
            font-size: 15px;
        }

        .highlight {
            color: #e74c3c;
            font-weight: bold;
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #34495e;
        }

        input[type="number"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }

        input[type="number"]:focus,
        select:focus {
            border-color: #27ae60;
            outline: none;
            box-shadow: 0 0 5px rgba(39, 174, 96, 0.3);
        }

        .btn-submit {
            width: 100%;
            background-color: #27ae60;
            color: #fff;
            padding: 14px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background-color: #219a52;
        }

        .btn-cancel {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #7f8c8d;
            text-decoration: none;
            font-size: 15px;
        }

        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            background-color: #fab1a0;
            color: #d63031;
        }
    </style>
</head>

<body>

    <div class="form-container">
        <h2>💸 Enregistrer un Paiement</h2>

        <div class="recap-box">
            <p><strong>Client :</strong> <?= htmlspecialchars($facture['nom'] . ' ' . $facture['prenom']) ?></p>
            <p><strong>Dossier :</strong> <?= htmlspecialchars($facture['description']) ?></p>
            <p><strong>Total Facture :</strong> <?= number_format($facture['montant_total'], 0, ',', ' ') ?> FCFA</p>
            <p><strong>Déjà payé :</strong> <?= number_format($facture['montant_paye'], 0, ',', ' ') ?> FCFA</p>
            <p class="highlight">Reste à payer : <?= number_format($reste_a_payer, 0, ',', ' ') ?> FCFA</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="payer.php?facture_id=<?= htmlspecialchars($facture_id) ?>">
            <div class="form-group">
                <label for="montant">Montant du paiement (FCFA) :</label>
                <input type="number" id="montant" name="montant" value="<?= $reste_a_payer ?>"
                    max="<?= $reste_a_payer ?>" min="1" required>
            </div>

            <div class="form-group">
                <label for="methode">Méthode de paiement :</label>
                <select id="methode" name="methode" required onchange="toggleReferenceField()">
                    <option value="Espèces">Espèces</option>
                    <option value="Chèque">Chèque</option>
                    <option value="Virement">Virement Bancaire</option>
                    <option value="Carte">Carte Bancaire</option>
                </select>
            </div>

            <div class="form-group" id="ref_group" style="display: none;">
                <label for="reference">Numéro de chèque / virement :</label>
                <input type="text" id="reference" name="reference" placeholder="Ex: CHQ12345" style="width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 5px; box-sizing: border-box; font-size: 16px;">
            </div>

            <button type="submit" class="btn-submit">Valider le paiement</button>
        </form>

        <a href="dossiers.php" class="btn-cancel">Annuler et retourner aux dossiers</a>
    </div>

    <script>
        function toggleReferenceField() {
            const methode = document.getElementById('methode').value;
            const refGroup = document.getElementById('ref_group');
            if (methode === 'Chèque' || methode === 'Virement') {
                refGroup.style.display = 'block';
            } else {
                refGroup.style.display = 'none';
                document.getElementById('reference').value = '';
            }
        }
        // Initialize state on load
        toggleReferenceField();
    </script>
</body>

</html>