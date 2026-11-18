<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
check_auth();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo       = trim($_POST['codigo'] ?? '');
    $nombre       = trim($_POST['nombre'] ?? '');
    $descripcion  = trim($_POST['descripcion'] ?? '');
    $precio       = floatval($_POST['precio'] ?? 0);
    $stock_actual = intval($_POST['stock_actual'] ?? 0);
    $stock_minimo = intval($_POST['stock_minimo'] ?? 0);
    $id_categoria = intval($_POST['id_categoria'] ?? 0);

    if (empty($codigo) || empty($nombre) || $id_categoria <= 0) {
        $error = "Por favor complete los campos obligatorios.";
    } elseif ($precio < 0 || $stock_actual < 0 || $stock_minimo < 0) {
        $error = "Los valores numéricos (precio y stock) no pueden ser negativos.";
    } else {
        try {
            $check = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE codigo = :codigo");
            $check->execute([':codigo' => $codigo]);
            if ($check->fetchColumn() > 0) {
                $error = "El código de producto '$codigo' ya existe en el sistema.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO productos (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, id_categoria, estado) VALUES (:c, :n, :d, :p, :sa, :sm, :cat, 1)");
                $stmt->execute([
                    ':c'   => $codigo,
                    ':n'   => $nombre,
                    ':d'   => $descripcion,
                    ':p'   => $precio,
                    ':sa'  => $stock_actual,
                    ':sm'  => $stock_minimo,
                    ':cat' => $id_categoria
                ]);
                header("Location: productos.php?msg=" . urlencode("Producto '$nombre' creado exitosamente."));
                exit;
            }
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
$categorias = $pdo->query("SELECT * FROM categorias WHERE estado=1")->fetchAll();
include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="container py-4">
    <div class="card mx-auto p-4 shadow-sm" style="max-width: 600px;">
        <h4 class="mb-3"><i class="bi bi-plus-circle text-primary"></i> Crear Nuevo Producto</h4>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form id="formProducto" method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Código (*)</label>
                <input type="text" name="codigo" id="codigo" class="form-control" required placeholder="Ej: PROD-004">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Nombre del Producto (*)</label>
                <input type="text" name="nombre" class="form-control" required placeholder="Ej: Teclado Mecánico RGB">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Categoría (*)</label>
                <select name="id_categoria" class="form-select" required>
                    <option value="">-- Seleccionar Categoría --</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Precio ($)</label>
                    <input type="number" step="0.01" name="precio" id="precio" class="form-control" value="0.00" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Stock Inicial</label>
                    <input type="number" name="stock_actual" id="stock_actual" class="form-control" value="0" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Stock Mínimo</label>
                    <input type="number" name="stock_minimo" class="form-control" value="5" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="2"></textarea>
            </div>
            <div class="d-flex justify-content-between">
                <a href="productos.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary fw-bold">Guardar Producto</button>
            </div>
        </form>
    </div>
</div>
<?php include 'includes/footer.php'; ?>