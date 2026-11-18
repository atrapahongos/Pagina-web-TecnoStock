<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
check_auth();

$total_prod = $pdo->query("SELECT COUNT(*) FROM productos WHERE estado = 1")->fetchColumn();
$total_alertas = $pdo->query("SELECT COUNT(*) FROM productos WHERE stock_actual < stock_minimo AND estado = 1")->fetchColumn();

$stmtAlerta = $pdo->query("
    SELECT p.*, c.nombre AS categoria 
    FROM productos p 
    INNER JOIN categorias c ON p.id_categoria = c.id_categoria 
    WHERE p.stock_actual < p.stock_minimo AND p.estado = 1
");
$productos_alerta = $stmtAlerta->fetchAll();

include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="container py-4">
    <h2 class="fw-bold mb-4"><i class="bi bi-speedometer2 text-primary"></i> Panel Principal TecnoStock</h2>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-white p-3 border-start border-primary border-4 shadow-sm">
                <h5>Productos Activos</h5>
                <h3 class="fw-bold"><?= $total_prod ?></h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-white p-3 border-start border-danger border-4 shadow-sm">
                <h5 class="text-danger">Alertas de Stock Bajo</h5>
                <h3 class="fw-bold text-danger"><?= $total_alertas ?></h3>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-danger text-white fw-bold">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> Productos con Stock Bajo el Mínimo
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>Código</th><th>Producto</th><th>Categoría</th><th>Stock Actual</th><th>Stock Mínimo</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($productos_alerta as $p): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
                        <td class="fw-bold"><?= htmlspecialchars($p['nombre']) ?></td>
                        <td><?= htmlspecialchars($p['categoria']) ?></td>
                        <td><span class="badge bg-danger fs-6"><?= $p['stock_actual'] ?></span></td>
                        <td><span class="badge bg-secondary"><?= $p['stock_minimo'] ?></span></td>
                        <td>
                            <a href="movimiento_nuevo.php?producto_id=<?= $p['id_producto'] ?>&tipo=ENTRADA" class="btn btn-sm btn-success">
                                <i class="bi bi-plus-lg"></i> Reabastecer
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>