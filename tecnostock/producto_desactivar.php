<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
check_auth();

$id = $_GET['id'] ?? 0;
if ($id) {
    $stmt = $pdo->prepare("UPDATE productos SET estado = 0 WHERE id_producto = :id");
    $stmt->execute([':id' => $id]);
}
header("Location: productos.php?msg=" . urlencode("Producto desactivado correctamente."));
exit;
?>