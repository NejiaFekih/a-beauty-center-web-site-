<?php
// Copy this file to config.php and fill in your real local values.
// config.php itself is excluded from git via .gitignore — never commit real credentials.

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "beautybar");

define("ADMIN_MOT_DE_PASSE", "change_this_password");

function getConnexionDB() {
    $c = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$c) {
        die("Database connection failed: " . mysqli_connect_error());
    }
    return $c;
}
?>
