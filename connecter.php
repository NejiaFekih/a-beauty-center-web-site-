<?php
session_start();
require_once 'config.php';

$email = $_POST["email"] ?? "";
$mp = $_POST["mp"] ?? "";

$c = getConnexionDB();

$req = mysqli_prepare($c, "SELECT * FROM client WHERE email = ? AND mp = ?");
mysqli_stmt_bind_param($req, "ss", $email, $mp);
mysqli_stmt_execute($req);
$res = mysqli_stmt_get_result($req);

if (mysqli_num_rows($res) === 1) {
    $utilisateur = mysqli_fetch_assoc($res);
    $_SESSION["connecte"] = true;
    $_SESSION["nom"] = $utilisateur["np"];
    $_SESSION["email"] = $utilisateur["email"];
    mysqli_stmt_close($req);
    mysqli_close($c);
    header("Location: page_d_utilisateur.php");
    exit();
} else {
    mysqli_stmt_close($req);
    mysqli_close($c);
    // Redirige vers la page principale avec un indicateur d'erreur,
    // au lieu d'afficher une page blanche.
    header("Location: page_d_utilisateur.php?erreur_connexion=1");
    exit();
}
?>
