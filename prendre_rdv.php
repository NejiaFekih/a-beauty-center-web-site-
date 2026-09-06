<?php
session_start();
require_once 'config.php';

// Sécurité : seul un utilisateur connecté peut prendre rendez-vous
if (!isset($_SESSION["connecte"]) || $_SESSION["connecte"] !== true) {
    header("Location: page_d_utilisateur.php");
    exit();
}

$np = $_SESSION["nom"];
$services = $_POST["services"] ?? [];
$num = trim($_POST["num"] ?? "");
$date_rdv = $_POST["date_rdv"] ?? "";
$heure_rdv = $_POST["heure_rdv"] ?? "";

// Validation simple
if (!is_array($services) || count($services) === 0 || $num === "" || $date_rdv === "" || $heure_rdv === "") {
    header("Location: page_d_utilisateur.php?erreur_rdv=champs#rendezvous");
    exit();
}

$dateComplete = $date_rdv . " " . $heure_rdv . ":00";

// On refuse une date déjà passée
if (strtotime($dateComplete) < time()) {
    header("Location: page_d_utilisateur.php?erreur_rdv=passee#rendezvous");
    exit();
}

// On refuse les horaires en dehors des heures d'ouverture (10h00 - 20h00)
if ($heure_rdv < "10:00" || $heure_rdv > "20:00") {
    header("Location: page_d_utilisateur.php?erreur_rdv=horaire#rendezvous");
    exit();
}

$c = getConnexionDB();

// Vérifie que TOUS les services choisis existent bien
foreach ($services as $service) {
    $reqService = mysqli_prepare($c, "SELECT service FROM les_services WHERE service = ?");
    mysqli_stmt_bind_param($reqService, "s", $service);
    mysqli_stmt_execute($reqService);
    $resService = mysqli_stmt_get_result($reqService);
    if (mysqli_num_rows($resService) === 0) {
        mysqli_stmt_close($reqService);
        mysqli_close($c);
        header("Location: page_d_utilisateur.php?erreur_rdv=service#rendezvous");
        exit();
    }
    mysqli_stmt_close($reqService);
}

// Un seul rendez-vous par jour et par client
$reqMemeJour = mysqli_prepare($c, "SELECT id FROM rendez_vous WHERE np = ? AND DATE(date) = ?");
mysqli_stmt_bind_param($reqMemeJour, "ss", $np, $date_rdv);
mysqli_stmt_execute($reqMemeJour);
$resMemeJour = mysqli_stmt_get_result($reqMemeJour);
if (mysqli_num_rows($resMemeJour) > 0) {
    mysqli_stmt_close($reqMemeJour);
    mysqli_close($c);
    header("Location: page_d_utilisateur.php?erreur_rdv=meme_jour#rendezvous");
    exit();
}
mysqli_stmt_close($reqMemeJour);

// Vérifie qu'il y a au moins 1h30 (90 minutes) avec tout autre rendez-vous existant
$reqConflit = mysqli_prepare($c, "SELECT np FROM rendez_vous WHERE np != ? AND ABS(TIMESTAMPDIFF(MINUTE, date, ?)) < 90");
mysqli_stmt_bind_param($reqConflit, "ss", $np, $dateComplete);
mysqli_stmt_execute($reqConflit);
$resConflit = mysqli_stmt_get_result($reqConflit);

if (mysqli_num_rows($resConflit) > 0) {
    mysqli_stmt_close($reqConflit);
    mysqli_close($c);
    header("Location: page_d_utilisateur.php?erreur_rdv=conflit#rendezvous");
    exit();
}
mysqli_stmt_close($reqConflit);

// Création du rendez-vous (id auto-généré) : vu=0, statut=en_attente, vu_client=1 (rien de nouveau pour lui-même)
$req = mysqli_prepare($c, "INSERT INTO rendez_vous (np, date, num, vu, statut, vu_client) VALUES (?, ?, ?, 0, 'en_attente', 1)");
mysqli_stmt_bind_param($req, "sss", $np, $dateComplete, $num);
mysqli_stmt_execute($req);
$idRdv = mysqli_insert_id($c);
mysqli_stmt_close($req);

// Rattacher chaque service choisi à ce rendez-vous
$reqServiceInsert = mysqli_prepare($c, "INSERT INTO rendez_vous_services (id_rdv, service) VALUES (?, ?)");
foreach ($services as $service) {
    mysqli_stmt_bind_param($reqServiceInsert, "is", $idRdv, $service);
    mysqli_stmt_execute($reqServiceInsert);
}
mysqli_stmt_close($reqServiceInsert);

mysqli_close($c);

header("Location: page_d_utilisateur.php?rdv=confirme#rendezvous");
exit();
?>
