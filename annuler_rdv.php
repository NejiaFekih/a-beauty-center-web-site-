<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["connecte"]) || $_SESSION["connecte"] !== true) {
    header("Location: page_d_utilisateur.php");
    exit();
}

$np = $_SESSION["nom"];
$id = isset($_POST["id"]) ? (int)$_POST["id"] : 0;

if ($id > 0) {
    $c = getConnexionDB();
    // On vérifie que ce rendez-vous appartient bien au client connecté avant de le supprimer
    $req = mysqli_prepare($c, "DELETE FROM rendez_vous WHERE id = ? AND np = ?");
    mysqli_stmt_bind_param($req, "is", $id, $np);
    mysqli_stmt_execute($req);
    mysqli_stmt_close($req);
    mysqli_close($c);
}

header("Location: page_d_utilisateur.php?rdv=annule#rendezvous");
exit();
?>
