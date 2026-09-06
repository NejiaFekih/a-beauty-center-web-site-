<?php
session_start();
require_once 'config.php';

// Sécurité : seul un utilisateur connecté peut publier un avis
if (!isset($_SESSION["connecte"]) || $_SESSION["connecte"] !== true) {
    header("Location: page_d_utilisateur.php");
    exit();
}

$texte = trim($_POST["texte"]);
$note = isset($_POST["note"]) ? (int)$_POST["note"] : 0;
$nom = $_SESSION["nom"];
$email = $_SESSION["email"];

// Validation simple
if ($texte === "" || $note < 1 || $note > 5) {
    echo "Merci de donner une note (1 à 5) et un commentaire avant de publier.";
    echo '<br><a href="page_d_utilisateur.php">Retour</a>';
    exit();
}

$c = getConnexionDB();

// Requête préparée pour éviter les injections SQL
$req = mysqli_prepare($c, "INSERT INTO avis (email, nom, texte, note, statut) VALUES (?, ?, ?, ?, 'en_attente')");
mysqli_stmt_bind_param($req, "sssi", $email, $nom, $texte, $note);
mysqli_stmt_execute($req);
mysqli_stmt_close($req);
mysqli_close($c);

header("Location: page_d_utilisateur.php?avis=envoye");
exit();
?>
