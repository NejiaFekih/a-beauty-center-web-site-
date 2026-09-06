<?php
session_start();
session_destroy();
header("Location: page_d_utilisateur.php");
exit();
?>
