<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
check_auth();

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto = :id");
$stmt->execute([':id' => $id]);
$producto = $stmt->fetch();

if (!$producto) {
    header("Location: productos.php?msg=" . urlencode("Producto no encontrado."));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre       = trim($_POST['nombre'] ?? '');
    $descripcion  = trim($_POST['descripcion'] ?? '');
    $precio       = floatval($_POST['precio'] ?? 0);
    $stock_minimo = intval($_POST['stock_minimo'] ?? 0);
    $id_categoria = intval($_POST['id_categoria'] ?? 0);

    if (empty($nombre) || $id_categoria <= 0) {
        $error = "El nombre y la categoría son obligatorios.";
    } elseif ($precio < 0 || $stock_minimo < 0) {
        $error = "El precio y el stock mínimo no pueden ser negativos.";
    } else {
        try {
            $stmtUp = $pdo->prepare("UPDATE productos SET nombre = :n, descripcion = :d, precio = :p, stock_minimo = :sm, id_categoria = :cat WHERE id_producto = :id");
            $stmtUp->execute([
                ':n'   => $nombre,
                ':d'   => $descripcion,
                ':p'   => $precio,
                ':sm'  => $stock_minimo,
                ':cat' => $id_categoria,
                ':id'  => $id
            ]);
            header("Location: productos.php?msg=" . urlencode("Producto '$nombre' actualizado correctamente."));
            exit;
        } catch (PDOException $e) {
            $error = "Error al actualizar: " . $e->getMessage();
        }
    }
}

$categorias = $pdo->query("SELECT * FROM categorias WHERE estado=1")->fetchAll();
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="container py-4">
    <div class="card mx-auto p-4 shadow-sm" style="max-width: 600px;">
        <h4 class="mb-3"><i class="bi bi-pencil-square text-primary"></i> Editar Producto</h4>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form id="formProducto" method="POST">
            <div class="mb-3">
                <label class="form-label">Código (No editable)</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($producto['codigo']) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Nombre (*)</label>
                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($producto['nombre']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Categoría (*)</label>
                <select name="id_categoria" class="form-select" required>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>" <?= $cat['id_categoria'] == $producto['id_categoria'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Precio ($)</label>
                    <input type="number" step="0.01" name="precio" id="precio" class="form-control" value="<?= $producto['precio'] ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Stock Mínimo</label>
                    <input type="number" name="stock_minimo" class="form-control" value="<?= $producto['stock_minimo'] ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="2"><?= htmlspecialchars($producto['descripcion']) ?></textarea>
            </div>
            <div class="d-flex justify-content-between">
                <a href="productos.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary fw-bold">Actualizar Producto</button>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>