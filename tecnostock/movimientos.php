<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
check_auth();

$msg = $_GET['msg'] ?? '';

$sql = "
    SELECT m.*, p.codigo, p.nombre AS producto, u.nombre AS usuario 
    FROM movimientos m 
    INNER JOIN productos p ON m.id_producto = p.id_producto 
    INNER JOIN usuarios u ON m.id_usuario = u.id_usuario 
    ORDER BY m.fecha_hora DESC
";
$movimientos = $pdo->query($sql)->fetchAll();

include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="bi bi-arrow-left-right text-primary"></i> Historial de Movimientos</h3>
        <a href="movimiento_nuevo.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> Registrar Movimiento</a>
    </div>

    <?php if ($msg): ?><div class="alert alert-info"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <table class="table table-bordered table-striped align-middle bg-white shadow-sm">
        <thead class="table-dark">
            <tr><th>Fecha / Hora</th><th>Tipo</th><th>Producto</th><th>Cantidad</th><th>Usuario</th><th>Observación</th></tr>
        </thead>
        <tbody>
            <?php foreach ($movimientos as $m): ?>
            <tr>
                <td><?= $m['fecha_hora'] ?></td>
                <td>
                    <?php if ($m['tipo'] === 'ENTRADA'): ?>
                        <span class="badge bg-success"><i class="bi bi-arrow-down-left"></i> ENTRADA</span>
                    <?php else: ?>
                        <span class="badge bg-danger"><i class="bi bi-arrow-up-right"></i> SALIDA</span>
                    <?php endif; ?>
                </td>
                <td><code><?= htmlspecialchars($m['codigo']) ?></code> - <?= htmlspecialchars($m['producto']) ?></td>
                <td class="fw-bold"><?= $m['cantidad'] ?></td>
                <td><?= htmlspecialchars($m['usuario']) ?></td>
                <td><?= htmlspecialchars($m['observacion'] ?: 'Sin observaciones') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include 'includes/footer.php'; ?>