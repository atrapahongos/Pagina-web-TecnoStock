<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_auth() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php?msg=" . urlencode("Debe iniciar sesión para acceder al sistema."));
        exit;
    }
}
?>