<?php
session_start();
require_once 'config.php';

// Sécurité : accès réservé aux administrateurs connectés
if (!isset($_SESSION["admin_connecte"]) || $_SESSION["admin_connecte"] !== true) {
    header("Location: admin_login.php");
    exit();
}

$c = getConnexionDB();

// Traiter une action (approuver ou supprimer) si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"]) && isset($_POST["action"])) {
    $id = (int)$_POST["id"];

    if ($_POST["action"] === "approuver") {
        $req = mysqli_prepare($c, "UPDATE avis SET statut='approuve' WHERE id=?");
        mysqli_stmt_bind_param($req, "i", $id);
        mysqli_stmt_execute($req);
        mysqli_stmt_close($req);
    } elseif ($_POST["action"] === "refuser") {
        $req = mysqli_prepare($c, "DELETE FROM avis WHERE id=?");
        mysqli_stmt_bind_param($req, "i", $id);
        mysqli_stmt_execute($req);
        mysqli_stmt_close($req);
    } elseif ($_POST["action"] === "masquer") {
        // Repasse un avis déjà approuvé en attente (le retire de l'affichage public)
        $req = mysqli_prepare($c, "UPDATE avis SET statut='en_attente' WHERE id=?");
        mysqli_stmt_bind_param($req, "i", $id);
        mysqli_stmt_execute($req);
        mysqli_stmt_close($req);
    }

    header("Location: admin_avis.php");
    exit();
}

// Récupérer les avis en attente
$enAttente = mysqli_query($c, "SELECT * FROM avis WHERE statut='en_attente' ORDER BY date_creation DESC");

// Récupérer les avis déjà approuvés
$approuves = mysqli_query($c, "SELECT * FROM avis WHERE statut='approuve' ORDER BY date_creation DESC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration des avis - BeautyBar Amal</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand&family=Poppins&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: poppins, sans-serif;
            background-color: #fff0f5;
            margin: 0;
            padding: 40px 20px;
        }
        .admin-header {
            max-width: 900px;
            margin: 0 auto 30px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-header h1 {
            color: #F28B82;
            font-family: "Pacifico", cursive;
            font-size: 28px;
        }
        .admin-header a {
            color: #555;
            text-decoration: none;
            font-size: 14px;
        }
        .admin-header a:hover {
            color: #F28B82;
        }
        .admin-section {
            max-width: 900px;
            margin: 0 auto 40px auto;
        }
        .admin-section h2 {
            color: #434343;
            font-size: 20px;
            border-bottom: 2px solid #F28B82;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        .avis-admin-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }
        .avis-admin-info h4 {
            margin: 0 0 5px 0;
            color: #434343;
        }
        .avis-admin-info .etoiles {
            color: gold;
            margin-bottom: 8px;
        }
        .avis-admin-info p {
            color: #555;
            margin: 0;
            font-size: 15px;
        }
        .avis-admin-info .meta {
            font-size: 12px;
            color: #999;
            margin-top: 8px;
        }
        .avis-admin-actions {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }
        .avis-admin-actions button {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-approuver {
            background: #99D1C9;
            color: white;
        }
        .btn-approuver:hover {
            background: #7bbdb2;
        }
        .btn-refuser {
            background: #e57373;
            color: white;
        }
        .btn-refuser:hover {
            background: #d15c5c;
        }
        .btn-masquer {
            background: #ccc;
            color: #333;
        }
        .btn-masquer:hover {
            background: #b3b3b3;
        }
        .vide {
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>

    <div class="admin-header">
        <h1>Gestion des avis</h1>
        <a href="admin_logout.php">Se déconnecter</a>
    </div>

    <div class="admin-section">
        <h2>En attente de validation</h2>
        <?php if (mysqli_num_rows($enAttente) === 0): ?>
            <p class="vide">Aucun avis en attente.</p>
        <?php else: ?>
            <?php while ($avis = mysqli_fetch_assoc($enAttente)): ?>
                <div class="avis-admin-card">
                    <div class="avis-admin-info">
                        <h4><?php echo htmlspecialchars($avis["nom"]); ?></h4>
                        <div class="etoiles"><?php echo str_repeat("⭐", (int)$avis["note"]); ?></div>
                        <p><?php echo htmlspecialchars($avis["texte"]); ?></p>
                        <div class="meta"><?php echo htmlspecialchars($avis["email"]); ?> — <?php echo htmlspecialchars($avis["date_creation"]); ?></div>
                    </div>
                    <div class="avis-admin-actions">
                        <form method="post">
                            <input type="hidden" name="id" value="<?php echo (int)$avis['id']; ?>">
                            <input type="hidden" name="action" value="approuver">
                            <button type="submit" class="btn-approuver">Approuver</button>
                        </form>
                        <form method="post">
                            <input type="hidden" name="id" value="<?php echo (int)$avis['id']; ?>">
                            <input type="hidden" name="action" value="refuser">
                            <button type="submit" class="btn-refuser">Refuser</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <div class="admin-section">
        <h2>Déjà publiés</h2>
        <?php if (mysqli_num_rows($approuves) === 0): ?>
            <p class="vide">Aucun avis publié pour le moment.</p>
        <?php else: ?>
            <?php while ($avis = mysqli_fetch_assoc($approuves)): ?>
                <div class="avis-admin-card">
                    <div class="avis-admin-info">
                        <h4><?php echo htmlspecialchars($avis["nom"]); ?></h4>
                        <div class="etoiles"><?php echo str_repeat("⭐", (int)$avis["note"]); ?></div>
                        <p><?php echo htmlspecialchars($avis["texte"]); ?></p>
                        <div class="meta"><?php echo htmlspecialchars($avis["email"]); ?> — <?php echo htmlspecialchars($avis["date_creation"]); ?></div>
                    </div>
                    <div class="avis-admin-actions">
                        <form method="post">
                            <input type="hidden" name="id" value="<?php echo (int)$avis['id']; ?>">
                            <input type="hidden" name="action" value="masquer">
                            <button type="submit" class="btn-masquer">Retirer</button>
                        </form>
                        <form method="post">
                            <input type="hidden" name="id" value="<?php echo (int)$avis['id']; ?>">
                            <input type="hidden" name="action" value="refuser">
                            <button type="submit" class="btn-refuser">Supprimer</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

</body>
</html>
<?php mysqli_close($c); ?>
