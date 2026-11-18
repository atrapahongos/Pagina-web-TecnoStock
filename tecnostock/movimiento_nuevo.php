<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
check_auth();

$selected_prod = $_GET['producto_id'] ?? 0;
$selected_tipo = $_GET['tipo'] ?? 'ENTRADA';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = $_POST['id_producto'];
    $tipo        = $_POST['tipo'];
    $cantidad    = intval($_POST['cantidad']);
    $observacion = trim($_POST['observacion']);
    $id_usuario  = $_SESSION['usuario_id'];

    if ($cantidad <= 0) {
        $error = "La cantidad debe ser mayor a 0.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmtP = $pdo->prepare("SELECT stock_actual, stock_minimo, nombre FROM productos WHERE id_producto = :id FOR UPDATE");
            $stmtP->execute([':id' => $id_producto]);
            $prod = $stmtP->fetch();

            if ($tipo === 'SALIDA' && ($prod['stock_actual'] - $cantidad) < 0) {
                $pdo->rollBack();
                $error = "Operación denegada: La salida dejaría el stock bajo cero (Stock disponible: {$prod['stock_actual']}).";
            } else {
                $stmtM = $pdo->prepare("INSERT INTO movimientos (fecha_hora, tipo, cantidad, observacion, id_producto, id_usuario) VALUES (NOW(), :t, :c, :o, :p, :u)");
                $stmtM->execute([':t'=>$tipo, ':c'=>$cantidad, ':o'=>$observacion, ':p'=>$id_producto, ':u'=>$id_usuario]);

                $nuevo_stock = ($tipo === 'ENTRADA') ? ($prod['stock_actual'] + $cantidad) : ($prod['stock_actual'] - $cantidad);
                $stmtU = $pdo->prepare("UPDATE productos SET stock_actual = :ns WHERE id_producto = :id");
                $stmtU->execute([':ns' => $nuevo_stock, ':id' => $id_producto]);

                $pdo->commit();
                header("Location: movimientos.php?msg=" . urlencode("Movimiento de $tipo registrado con éxito para '{$prod['nombre']}'. Nuevo stock: $nuevo_stock"));
                exit;
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = "Error al procesar movimiento: " . $e->getMessage();
        }
    }
}
$productos = $pdo->query("SELECT * FROM productos WHERE estado = 1")->fetchAll();
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="container py-4">
    <div class="card mx-auto p-4 shadow-sm" style="max-width: 600px;">
        <h4 class="mb-3"><i class="bi bi-arrow-left-right text-primary"></i> Registrar Entrada / Salida</h4>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form id="formMovimiento" method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Producto</label>
                <select name="id_producto" id="id_producto" class="form-select" required>
                    <?php foreach ($productos as $p): ?>
                        <option value="<?= $p['id_producto'] ?>" data-stock="<?= $p['stock_actual'] ?>" <?= $p['id_producto'] == $selected_prod ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nombre']) ?> (Disponible: <?= $p['stock_actual'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Tipo de Movimiento</label>
                <select name="tipo" id="tipo" class="form-select">
                    <option value="ENTRADA" <?= $selected_tipo === 'ENTRADA' ? 'selected' : '' ?>>ENTRADA (+ Stock)</option>
                    <option value="SALIDA" <?= $selected_tipo === 'SALIDA' ? 'selected' : '' ?>>SALIDA (- Stock)</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Cantidad</label>
                <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" required value="1">
            </div>
            <div class="mb-3">
                <label class="form-label">Observación / Motivo</label>
                <textarea name="observacion" class="form-control" rows="2" placeholder="Ej: Importación proveedor, Venta cliente corporativo..."></textarea>
            </div>
            <div class="d-flex justify-content-between">
                <a href="movimientos.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success fw-bold">Guardar Movimiento</button>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>