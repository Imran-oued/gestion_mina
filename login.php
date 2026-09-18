<?php
// login.php
session_start();

// Si l'utilisateur est déjà connecté, on le redirige directement vers l'accueil
if (isset($_SESSION['connecte']) && $_SESSION['connecte'] === true) {
    header("Location: index.php");
    exit();
}

$message = '';
$messageType = '';

// Traitement du formulaire de connexion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $utilisateur = $_POST['utilisateur'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    // Identifiants d'accès codés en dur pour la Direction
    $utilisateur_valide = 'direction';
    $mdp_valide = 'mina2026';

    if ($utilisateur === $utilisateur_valide && $mot_de_passe === $mdp_valide) {
        // Authentification réussie !
        $_SESSION['connecte'] = true;
        $_SESSION['utilisateur'] = $utilisateur;
        
        // Redirection vers le tableau de bord
        header("Location: index.php");
        exit();
    } else {
        // Authentification échouée
        $message = "Identifiant ou mot de passe incorrect.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Mina Voyage</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .login-container {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border-top: 5px solid #2c3e50;
        }
        .login-container img {
            max-width: 160px;
            margin-bottom: 15px;
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 30px;
            margin-top: 10px;
            font-weight: bold;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #34495e;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 14px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }
        .btn-submit {
            width: 100%;
            background-color: #2c3e50;
            color: #fff;
            padding: 15px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 15px;
        }
        .btn-submit:hover {
            background-color: #1a252f;
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
        .footer-text {
            margin-top: 30px;
            font-size: 12px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>

<div class="login-container">
    <!-- Affichage du logo -->
    <img src="LOGO.jpg" alt="Logo Mina Voyage" onerror="this.style.display='none'">
    
    <h2>Espace Direction</h2>
    
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="utilisateur">Nom d'utilisateur :</label>
            <input type="text" id="utilisateur" name="utilisateur" placeholder="Entrez votre identifiant" required autofocus>
        </div>
        
        <div class="form-group">
            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Entrez votre mot de passe" required>
        </div>
        
        <button type="submit" class="btn-submit">Se connecter 🔒</button>
    </form>
    
    <div class="footer-text">
        &copy; <?= date('Y') ?> Mina Voyage - Accès Sécurisé
    </div>
</div>

</body>
</html>
