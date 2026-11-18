<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
check_auth();

$buscar = trim($_GET['buscar'] ?? '');
$categoria_id = trim($_GET['categoria_id'] ?? '');

$sql = "SELECT p.*, c.nombre AS categoria FROM productos p INNER JOIN categorias c ON p.id_categoria = c.id_categoria WHERE 1=1";
$params = [];

if (!empty($buscar)) {
    // Dividir la búsqueda por palabras clave para hacerla súper intuitiva
    $palabras = explode(' ', $buscar);
    $idx = 0;
    foreach ($palabras as $p_word) {
        $p_word = trim($p_word);
        if ($p_word !== '') {
            $k_nombre = ":b_nom_" . $idx;
            $k_codigo = ":b_cod_" . $idx;
            $k_desc   = ":b_desc_" . $idx;
            $k_cat    = ":b_cat_" . $idx;
            
            $sql .= " AND (p.nombre LIKE $k_nombre OR p.codigo LIKE $k_codigo OR p.descripcion LIKE $k_desc OR c.nombre LIKE $k_cat)";
            
            $paramVal = "%" . $p_word . "%";
            $params[$k_nombre] = $paramVal;
            $params[$k_codigo] = $paramVal;
            $params[$k_desc]   = $paramVal;
            $params[$k_cat]    = $paramVal;
            $idx++;
        }
    }
}

if (!empty($categoria_id)) {
    $sql .= " AND p.id_categoria = :cat_id";
    $params[':cat_id'] = $categoria_id;
}

$sql .= " ORDER BY p.estado DESC, p.nombre ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

$categorias = $pdo->query("SELECT * FROM categorias WHERE estado=1")->fetchAll();
$msg = $_GET['msg'] ?? '';

include 'includes/header.php';
include 'includes/navbar.php';
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="bi bi-box-seam"></i> Catálogo de Productos</h3>
        <a href="producto_crear.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nuevo Producto</a>
    </div>

    <?php if ($msg): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <form class="row g-2 mb-4" method="GET" action="productos.php">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="buscar" class="form-control" placeholder="Buscar por palabra clave (ej: Cable, Bluetooth, PROD-001)..." value="<?= htmlspecialchars($buscar) ?>">
            </div>
        </div>
        <div class="col-md-4">
            <select name="categoria_id" class="form-select">
                <option value="">-- Todas las Categorías --</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= $c['id_categoria'] ?>" <?= $categoria_id == $c['id_categoria'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-dark w-100"><i class="bi bi-funnel"></i> Filtrar</button>
            <?php if (!empty($buscar) || !empty($categoria_id)): ?>
                <a href="productos.php" class="btn btn-outline-secondary" title="Limpiar filtro"><i class="bi bi-x-circle"></i></a>
            <?php endif; ?>
        </div>
    </form>

    <table class="table table-bordered align-middle bg-white shadow-sm">
        <thead class="table-dark">
            <tr><th>Código</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php if (count($productos) === 0): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted"><i class="bi bi-search fs-3 d-block mb-2"></i>No se encontraron productos que coincidan con la búsqueda.</td></tr>
            <?php else: ?>
                <?php foreach ($productos as $p): ?>
                <tr class="<?= $p['estado'] == 0 ? 'table-secondary' : '' ?>">
                    <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
                    <td>
                        <strong><?= htmlspecialchars($p['nombre']) ?></strong>
                        <?php if ($p['descripcion']): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($p['descripcion']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['categoria']) ?></span></td>
                    <td>$<?= number_format($p['precio'], 0, ',', '.') ?></td>
                    <td>
                        <?php if ($p['stock_actual'] < $p['stock_minimo'] && $p['estado'] == 1): ?>
                            <span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> <?= $p['stock_actual'] ?> (Mín: <?= $p['stock_minimo'] ?>)</span>
                        <?php else: ?>
                            <span class="badge bg-success"><?= $p['stock_actual'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= $p['estado'] == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' ?></td>
                    <td>
                        <?php if ($p['estado'] == 1): ?>
                            <a href="producto_editar.php?id=<?= $p['id_producto'] ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                            <a href="producto_desactivar.php?id=<?= $p['id_producto'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Desactivar producto?')" title="Desactivar"><i class="bi bi-slash-circle"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include 'includes/footer.php'; ?>