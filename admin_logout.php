<?php
session_start();
unset($_SESSION["admin_connecte"]);
header("Location: admin_login.php");
exit();
?>
