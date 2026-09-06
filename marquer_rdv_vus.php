<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION["admin_connecte"]) || $_SESSION["admin_connecte"] !== true) {
    http_response_code(403);
    exit();
}

$c = getConnexionDB();
mysqli_query($c, "UPDATE rendez_vous SET vu = 1 WHERE vu = 0");
mysqli_close($c);

echo "ok";
?>
