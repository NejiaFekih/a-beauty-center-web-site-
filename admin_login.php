<?php
session_start();
require_once 'config.php';
// The real admin password now lives in config.php (excluded from git).

$erreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mp = $_POST["mp"] ?? "";
    if ($mp === ADMIN_MOT_DE_PASSE) {
        $_SESSION["admin_connecte"] = true;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $erreur = "Mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - BeautyBar Amal</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand&family=Poppins&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: poppins, sans-serif;
            background-color: #fff0f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .admin-login {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            width: 320px;
            text-align: center;
        }
        .admin-login h2 {
            color: #F28B82;
            font-family: "Pacifico", cursive;
            margin-bottom: 20px;
        }
        .admin-login input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .admin-login button {
            width: 100%;
            padding: 10px;
            background: #F28B82;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .admin-login button:hover {
            background: #99D1C9;
        }
        .erreur {
            color: #c0392b;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="admin-login">
        <h2>Espace Admin</h2>
        <?php if ($erreur): ?>
            <p class="erreur"><?php echo htmlspecialchars($erreur); ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="password" name="mp" placeholder="Mot de passe admin" required>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>
