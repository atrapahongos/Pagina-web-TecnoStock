<?php
if (session_status() === PHP_SESSION_NONE) session_start();
session_unset();
session_destroy();
header("Location: login.php?msg=" . urlencode("Sesión cerrada correctamente."));
exit;
?>