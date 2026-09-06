<?php
session_start();
require_once 'config.php';

// Sécurité : accès réservé aux administrateurs connectés
if (!isset($_SESSION["admin_connecte"]) || $_SESSION["admin_connecte"] !== true) {
    header("Location: admin_login.php");
    exit();
}

$c = getConnexionDB();

// ------------------------------------------------------------
// Traitement des actions (avis ou rendez-vous), quel que soit
// l'onglet où on se trouvait au moment du clic
// ------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["type"]) && isset($_POST["action"])) {

    if ($_POST["type"] === "avis" && isset($_POST["id"])) {
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
            $req = mysqli_prepare($c, "UPDATE avis SET statut='en_attente' WHERE id=?");
            mysqli_stmt_bind_param($req, "i", $id);
            mysqli_stmt_execute($req);
            mysqli_stmt_close($req);
        }
        header("Location: admin_dashboard.php#avis");
        exit();
    }

    if ($_POST["type"] === "rdv" && isset($_POST["id"])) {
        $idRdv = (int)$_POST["id"];

        if ($_POST["action"] === "annuler") {
            $req = mysqli_prepare($c, "DELETE FROM rendez_vous WHERE id=?");
            mysqli_stmt_bind_param($req, "i", $idRdv);
            mysqli_stmt_execute($req);
            mysqli_stmt_close($req);
        } elseif ($_POST["action"] === "confirmer") {
            $req = mysqli_prepare($c, "UPDATE rendez_vous SET statut='confirme', vu_client=0 WHERE id=?");
            mysqli_stmt_bind_param($req, "i", $idRdv);
            mysqli_stmt_execute($req);
            mysqli_stmt_close($req);
        } elseif ($_POST["action"] === "refuser_rdv") {
            $req = mysqli_prepare($c, "UPDATE rendez_vous SET statut='refuse', vu_client=0 WHERE id=?");
            mysqli_stmt_bind_param($req, "i", $idRdv);
            mysqli_stmt_execute($req);
            mysqli_stmt_close($req);
        }
        header("Location: admin_dashboard.php#rendezvous");
        exit();
    }
}

// ------------------------------------------------------------
// Récupération des données à afficher
// ------------------------------------------------------------
$enAttente = mysqli_query($c, "SELECT * FROM avis WHERE statut='en_attente' ORDER BY date_creation DESC");
$approuves = mysqli_query($c, "SELECT * FROM avis WHERE statut='approuve' ORDER BY date_creation DESC");

$rendezVousAVenir = mysqli_query($c, "
    SELECT rendez_vous.id, rendez_vous.np, rendez_vous.date, rendez_vous.num, rendez_vous.statut,
           GROUP_CONCAT(rendez_vous_services.service SEPARATOR ' + ') AS services,
           SUM(les_services.prix) AS total_prix
    FROM rendez_vous
    LEFT JOIN rendez_vous_services ON rendez_vous_services.id_rdv = rendez_vous.id
    LEFT JOIN les_services ON rendez_vous_services.service = les_services.service
    WHERE rendez_vous.date >= NOW()
    GROUP BY rendez_vous.id
    ORDER BY rendez_vous.date ASC
");

$resNouveaux = mysqli_query($c, "SELECT COUNT(*) AS total FROM rendez_vous WHERE vu = 0");
$nbRdvNouveaux = mysqli_fetch_assoc($resNouveaux)["total"];

$rendezVousPasses = mysqli_query($c, "
    SELECT rendez_vous.np, rendez_vous.date, rendez_vous.num,
           GROUP_CONCAT(rendez_vous_services.service SEPARATOR ' + ') AS services
    FROM rendez_vous
    LEFT JOIN rendez_vous_services ON rendez_vous_services.id_rdv = rendez_vous.id
    WHERE rendez_vous.date < NOW()
    GROUP BY rendez_vous.id
    ORDER BY rendez_vous.date DESC
    LIMIT 20
");

$nbEnAttente = mysqli_num_rows($enAttente);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord admin - BeautyBar Amal</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand&family=Poppins&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: poppins, sans-serif;
            background-color: #fff0f5;
            margin: 0;
            padding: 0;
        }
        .admin-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .admin-header h1 {
            color: #F28B82;
            font-family: "Pacifico", cursive;
            font-size: 26px;
            margin: 0;
        }
        .admin-header a {
            color: #555;
            text-decoration: none;
            font-size: 14px;
        }
        .admin-header a:hover {
            color: #F28B82;
        }

        .admin-tabs {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 25px 20px 0 20px;
        }
        .admin-tab-btn {
            padding: 10px 24px;
            border: none;
            border-radius: 25px;
            background: white;
            color: #555;
            cursor: pointer;
            font-family: poppins;
            font-size: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            position: relative;
        }
        .admin-tab-btn.actif {
            background: #F28B82;
            color: white;
        }
        .badge {
            background: #e57373;
            color: white;
            font-size: 11px;
            border-radius: 10px;
            padding: 1px 7px;
            margin-left: 6px;
        }
        .admin-tab-btn.actif .badge {
            background: white;
            color: #F28B82;
        }

        .admin-content {
            max-width: 950px;
            margin: 0 auto;
            padding: 30px 20px 60px 20px;
        }
        .admin-page {
            display: none;
        }
        .admin-page.actif {
            display: block;
        }

        .admin-section h2 {
            color: #434343;
            font-size: 20px;
            border-bottom: 2px solid #F28B82;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }

        .carte {
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
        .carte-info h4 {
            margin: 0 0 5px 0;
            color: #434343;
        }
        .carte-info .etoiles {
            color: gold;
            margin-bottom: 8px;
        }
        .carte-info p {
            color: #555;
            margin: 0;
            font-size: 15px;
        }
        .carte-info .meta {
            font-size: 12px;
            color: #999;
            margin-top: 8px;
        }
        .carte-actions {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }
        .carte-actions button {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-approuver { background: #99D1C9; color: white; }
        .btn-approuver:hover { background: #7bbdb2; }
        .btn-refuser { background: #e57373; color: white; }
        .btn-refuser:hover { background: #d15c5c; }
        .btn-masquer { background: #ccc; color: #333; }
        .btn-masquer:hover { background: #b3b3b3; }

        .rdv-tag {
            display: inline-block;
            background: #f8c4d4;
            color: #434343;
            font-size: 13px;
            padding: 2px 10px;
            border-radius: 10px;
            margin-left: 8px;
        }

        .vide {
            color: #888;
            font-style: italic;
        }

        table.rdv-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        table.rdv-table th, table.rdv-table td {
            padding: 12px 15px;
            text-align: left;
            font-size: 14px;
        }
        table.rdv-table th {
            background: #F28B82;
            color: white;
        }
        table.rdv-table tr:nth-child(even) {
            background: #fff8fa;
        }
        table.rdv-table td.prix {
            font-weight: 600;
            color: #434343;
        }

        .statut-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .statut-attente {
            background: #fff3cd;
            color: #8a6d1a;
        }
        .statut-confirme {
            background: #d4f4e2;
            color: #1a7a4c;
        }
        .statut-refuse {
            background: #fdecea;
            color: #c0392b;
        }
    </style>
</head>
<body>

    <div class="admin-header">
        <h1>Tableau de bord — BeautyBar Amal</h1>
        <a href="admin_logout.php">Se déconnecter</a>
    </div>

    <div class="admin-tabs">
        <button class="admin-tab-btn actif" data-cible="page-avis" type="button">
            Avis <?php if ($nbEnAttente > 0): ?><span class="badge"><?php echo $nbEnAttente; ?></span><?php endif; ?>
        </button>
        <button class="admin-tab-btn" data-cible="page-rdv" type="button">
            Rendez-vous <?php if ($nbRdvNouveaux > 0): ?><span class="badge" id="badgeRdv"><?php echo $nbRdvNouveaux; ?></span><?php endif; ?>
        </button>
    </div>

    <div class="admin-content">

        <!-- ===================== ONGLET AVIS ===================== -->
        <div class="admin-page actif" id="page-avis">

            <div class="admin-section">
                <h2>Avis en attente de validation</h2>
                <?php if ($nbEnAttente === 0): ?>
                    <p class="vide">Aucun avis en attente.</p>
                <?php else: mysqli_data_seek($enAttente, 0); while ($avis = mysqli_fetch_assoc($enAttente)): ?>
                    <div class="carte">
                        <div class="carte-info">
                            <h4><?php echo htmlspecialchars($avis["nom"]); ?></h4>
                            <div class="etoiles"><?php echo str_repeat("⭐", (int)$avis["note"]); ?></div>
                            <p><?php echo htmlspecialchars($avis["texte"]); ?></p>
                            <div class="meta"><?php echo htmlspecialchars($avis["email"]); ?> — <?php echo htmlspecialchars($avis["date_creation"]); ?></div>
                        </div>
                        <div class="carte-actions">
                            <form method="post">
                                <input type="hidden" name="type" value="avis">
                                <input type="hidden" name="id" value="<?php echo (int)$avis['id']; ?>">
                                <input type="hidden" name="action" value="approuver">
                                <button type="submit" class="btn-approuver">Approuver</button>
                            </form>
                            <form method="post">
                                <input type="hidden" name="type" value="avis">
                                <input type="hidden" name="id" value="<?php echo (int)$avis['id']; ?>">
                                <input type="hidden" name="action" value="refuser">
                                <button type="submit" class="btn-refuser">Refuser</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; endif; ?>
            </div>

            <div class="admin-section">
                <h2>Avis déjà publiés</h2>
                <?php if (mysqli_num_rows($approuves) === 0): ?>
                    <p class="vide">Aucun avis publié pour le moment.</p>
                <?php else: while ($avis = mysqli_fetch_assoc($approuves)): ?>
                    <div class="carte">
                        <div class="carte-info">
                            <h4><?php echo htmlspecialchars($avis["nom"]); ?></h4>
                            <div class="etoiles"><?php echo str_repeat("⭐", (int)$avis["note"]); ?></div>
                            <p><?php echo htmlspecialchars($avis["texte"]); ?></p>
                            <div class="meta"><?php echo htmlspecialchars($avis["email"]); ?> — <?php echo htmlspecialchars($avis["date_creation"]); ?></div>
                        </div>
                        <div class="carte-actions">
                            <form method="post">
                                <input type="hidden" name="type" value="avis">
                                <input type="hidden" name="id" value="<?php echo (int)$avis['id']; ?>">
                                <input type="hidden" name="action" value="masquer">
                                <button type="submit" class="btn-masquer">Retirer</button>
                            </form>
                            <form method="post">
                                <input type="hidden" name="type" value="avis">
                                <input type="hidden" name="id" value="<?php echo (int)$avis['id']; ?>">
                                <input type="hidden" name="action" value="refuser">
                                <button type="submit" class="btn-refuser">Supprimer</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; endif; ?>
            </div>

        </div>

        <!-- ===================== ONGLET RENDEZ-VOUS ===================== -->
        <div class="admin-page" id="page-rdv">

            <div class="admin-section">
                <h2>Rendez-vous à venir</h2>
                <?php if (mysqli_num_rows($rendezVousAVenir) === 0): ?>
                    <p class="vide">Aucun rendez-vous prévu.</p>
                <?php else: ?>
                    <table class="rdv-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Date &amp; heure</th>
                                <th>Téléphone</th>
                                <th>Prix</th>
                                <th>Statut</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            mysqli_data_seek($rendezVousAVenir, 0);
                            $badgesStatut = [
                                'en_attente' => '<span class="statut-badge statut-attente">En attente</span>',
                                'confirme'   => '<span class="statut-badge statut-confirme">Confirmé</span>',
                                'refuse'     => '<span class="statut-badge statut-refuse">Refusé</span>',
                            ];
                            while ($rdv = mysqli_fetch_assoc($rendezVousAVenir)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($rdv["np"]); ?></td>
                                    <td><?php echo htmlspecialchars($rdv["services"] ?? "—"); ?></td>
                                    <td><?php echo date("d/m/Y à H:i", strtotime($rdv["date"])); ?></td>
                                    <td><?php echo htmlspecialchars($rdv["num"]); ?></td>
                                    <td class="prix"><?php echo $rdv["total_prix"] !== null ? (int)$rdv["total_prix"] . " DT" : "—"; ?></td>
                                    <td><?php echo $badgesStatut[$rdv["statut"]] ?? htmlspecialchars($rdv["statut"]); ?></td>
                                    <td>
                                        <div class="carte-actions">
                                            <?php if ($rdv["statut"] !== "confirme"): ?>
                                            <form method="post">
                                                <input type="hidden" name="type" value="rdv">
                                                <input type="hidden" name="id" value="<?php echo (int)$rdv['id']; ?>">
                                                <input type="hidden" name="action" value="confirmer">
                                                <button type="submit" class="btn-approuver">Confirmer</button>
                                            </form>
                                            <?php endif; ?>
                                            <?php if ($rdv["statut"] !== "refuse"): ?>
                                            <form method="post">
                                                <input type="hidden" name="type" value="rdv">
                                                <input type="hidden" name="id" value="<?php echo (int)$rdv['id']; ?>">
                                                <input type="hidden" name="action" value="refuser_rdv">
                                                <button type="submit" class="btn-masquer">Refuser</button>
                                            </form>
                                            <?php endif; ?>
                                            <form method="post" onsubmit="return confirm('Supprimer définitivement ce rendez-vous ?');">
                                                <input type="hidden" name="type" value="rdv">
                                                <input type="hidden" name="id" value="<?php echo (int)$rdv['id']; ?>">
                                                <input type="hidden" name="action" value="annuler">
                                                <button type="submit" class="btn-refuser">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="admin-section">
                <h2>Historique récent (20 derniers)</h2>
                <?php if (mysqli_num_rows($rendezVousPasses) === 0): ?>
                    <p class="vide">Aucun rendez-vous passé.</p>
                <?php else: ?>
                    <table class="rdv-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Date &amp; heure</th>
                                <th>Téléphone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($rdv = mysqli_fetch_assoc($rendezVousPasses)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($rdv["np"]); ?></td>
                                    <td><?php echo htmlspecialchars($rdv["services"] ?? '—'); ?></td>
                                    <td><?php echo date("d/m/Y à H:i", strtotime($rdv["date"])); ?></td>
                                    <td><?php echo htmlspecialchars($rdv["num"]); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        </div>

    </div>

    <script>
        const boutonsOnglets = document.querySelectorAll('.admin-tab-btn');
        const pages = document.querySelectorAll('.admin-page');

        function ouvrirOnglet(cible) {
            boutonsOnglets.forEach(b => b.classList.toggle('actif', b.dataset.cible === cible));
            pages.forEach(p => p.classList.toggle('actif', p.id === cible));

            if (cible === 'page-rdv') {
                const badge = document.getElementById('badgeRdv');
                if (badge) badge.remove();
                fetch('marquer_rdv_vus.php').catch(() => {});
            }
        }

        boutonsOnglets.forEach(bouton => {
            bouton.addEventListener('click', () => ouvrirOnglet(bouton.dataset.cible));
        });

        // Ouvre directement l'onglet Rendez-vous si on arrive via #rendezvous
        if (window.location.hash === '#rendezvous') {
            ouvrirOnglet('page-rdv');
        }
    </script>

</body>
</html>
<?php mysqli_close($c); ?>
