<?php
// ajouter_client.php
require_once 'securite.php';
require_once 'db.php';

$message = '';
$messageType = '';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve data from the form
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $telephone = $_POST['telephone'] ?? '';

    // Basic validation to ensure fields aren't empty
    if (!empty($nom) && !empty($prenom) && !empty($telephone)) {
        try {
            // Prepare the INSERT query
            $sql = "INSERT INTO clients (nom, prenom, telephone) VALUES (:nom, :prenom, :telephone)";
            $stmt = $pdo->prepare($sql);

            // Bind the parameters
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':prenom', $prenom);
            $stmt->bindParam(':telephone', $telephone);

            // Execute the query
            if ($stmt->execute()) {
                // If success, redirect to index.php
                header("Location: index.php");
                exit();
            } else {
                $message = "Une erreur est survenue lors de l'enregistrement.";
                $messageType = "error";
            }
        } catch (PDOException $e) {
            $message = "Erreur SQL : " . $e->getMessage();
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
    <title>Ajouter un Client - Mina Voyage</title>
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
            max-width: 450px;
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
        input[type="tel"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="tel"]:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }

        .btn-submit {
            width: 100%;
            background-color: #2ecc71;
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
            background-color: #27ae60;
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
        <h2>👤 Nouveau Client</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="ajouter_client.php">
            <div class="form-group">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" placeholder="Ex: Dupont" required>
            </div>

            <div class="form-group">
                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" placeholder="Ex: Jean" required>
            </div>

            <div class="form-group">
                <label for="telephone">Téléphone :</label>
                <input type="tel" id="telephone" name="telephone" placeholder="Ex: 06 12 34 56 78" required>
            </div>

            <button type="submit" class="btn-submit">Enregistrer le client</button>
        </form>

        <a href="index.php" class="btn-cancel">← Retour au tableau de bord</a>
    </div>

</body>

</html>