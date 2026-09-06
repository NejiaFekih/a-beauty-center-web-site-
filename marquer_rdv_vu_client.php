<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["connecte"]) || $_SESSION["connecte"] !== true) {
    http_response_code(403);
    exit();
}

$np = $_SESSION["nom"];

$c = getConnexionDB();
$req = mysqli_prepare($c, "UPDATE rendez_vous SET vu_client = 1 WHERE np = ?");
mysqli_stmt_bind_param($req, "s", $np);
mysqli_stmt_execute($req);
mysqli_stmt_close($req);
mysqli_close($c);

echo "ok";
?>
