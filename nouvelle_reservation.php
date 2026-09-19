<?php
// nouvelle_reservation.php
require_once 'securite.php';
require_once 'db.php';

$message = '';
$messageType = '';

// Récupérer la liste des clients pour le menu déroulant
try {
    $stmt = $pdo->query('SELECT id, nom, prenom FROM clients ORDER BY nom, prenom');
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $clients = [];
    $message = "Erreur de base de données : " . $e->getMessage();
    $messageType = "error";
}

// Traitement du formulaire à la soumission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = $_POST['client_id'] ?? '';
    $description = $_POST['description'] ?? '';
    $montant_total = $_POST['montant_total'] ?? '';

    if (!empty($client_id) && !empty($description) && !empty($montant_total)) {
        try {
            // Optionnel mais recommandé : on utilise une transaction pour s'assurer que
            // les deux insertions (réservation et facture) se font correctement ensemble.
            $pdo->beginTransaction();

            $fichier_joint = null;
            if (isset($_FILES['fichier_joint']) && $_FILES['fichier_joint']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($_FILES['fichier_joint']['name']));
                $uploadFile = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['fichier_joint']['tmp_name'], $uploadFile)) {
                    $fichier_joint = $uploadFile;
                }
            }

            // 1. Insertion dans la table reservations
            $sqlRes = "INSERT INTO reservations (client_id, description, fichier_joint) VALUES (:client_id, :description, :fichier_joint)";
            $stmtRes = $pdo->prepare($sqlRes);
            $stmtRes->bindParam(':client_id', $client_id);
            $stmtRes->bindParam(':description', $description);
            $stmtRes->bindParam(':fichier_joint', $fichier_joint);
            $stmtRes->execute();

            // 2. Récupérer l'ID de la réservation que l'on vient de créer
            $reservation_id = $pdo->lastInsertId();

            // 3. Insertion dans la table factures
            $sqlFact = "INSERT INTO factures (reservation_id, montant_total, montant_paye, statut_paiement) 
                        VALUES (:reservation_id, :montant_total, 0, 'impayé')";
            $stmtFact = $pdo->prepare($sqlFact);
            $stmtFact->bindParam(':reservation_id', $reservation_id);
            $stmtFact->bindParam(':montant_total', $montant_total);
            $stmtFact->execute();

            // Si tout s'est bien passé, on valide la transaction
            $pdo->commit();

            // Redirection vers le tableau de bord
            header("Location: index.php");
            exit();
        } catch (PDOException $e) {
            // En cas de problème, on annule tout
            $pdo->rollBack();
            $message = "Erreur lors de la création du dossier : " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = "Veuillez remplir tous les champs du formulaire.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Dossier - Mina Voyage</title>
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
            margin-bottom: 30px;
            margin-top: 0;
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

        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus {
            border-color: #f39c12;
            /* Changement de couleur pour différencier du client */
            outline: none;
            box-shadow: 0 0 5px rgba(243, 156, 18, 0.3);
        }

        .btn-submit {
            width: 100%;
            background-color: #f39c12;
            /* Couleur orange/jaune pour les dossiers */
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
            background-color: #d68910;
        }

        .btn-cancel {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #7f8c8d;
            text-decoration: none;
            font-size: 15px;
            transition: color 0.3s;
        }

        .btn-cancel:hover {
            color: #2c3e50;
            text-decoration: underline;
        }

        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .alert-error {
            background-color: #fab1a0;
            color: #d63031;
            border: 1px solid #ff7675;
        }
    </style>
</head>

<body>

    <div class="form-container">
        <h2>📁 Nouveau Dossier</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="nouvelle_reservation.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="client_id">Client :</label>
                <select id="client_id" name="client_id" required>
                    <option value="">-- Sélectionnez un client --</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= htmlspecialchars($client['id']) ?>">
                            <?= htmlspecialchars($client['nom'] . ' ' . $client['prenom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description du service :</label>
                <input type="text" id="description" name="description" placeholder="Ex: Billet d'avion Ouaga-Paris"
                    required>
            </div>

            <div class="form-group">
                <label for="montant_total">Montant total de la facture (FCFA) :</label>
                <input type="number" id="montant_total" name="montant_total" placeholder="Ex: 450000" min="0" required>
            </div>

            <div class="form-group">
                <label for="fichier_joint">Pièce jointe (Optionnel) :</label>
                <input type="file" id="fichier_joint" name="fichier_joint" accept=".pdf,.jpg,.jpeg,.png" style="padding: 10px; background: #fff;">
            </div>

            <button type="submit" class="btn-submit">Créer le dossier</button>
        </form>

        <a href="index.php" class="btn-cancel">← Retour au tableau de bord</a>
    </div>

</body>

</html>