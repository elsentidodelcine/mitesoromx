<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$pageTitle = 'Productos';
$pdo = db();
$accion = $_GET['accion'] ?? 'lista';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$filtroTienda = isset($_GET['tienda_id']) ? (int) $_GET['tienda_id'] : 0;

// Guardar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        flash('error', 'Token inválido.');
        redirect('/admin/productos.php');
    }

    $editId     = (int) ($_POST['id'] ?? 0);
    $tiendaId   = (int) ($_POST['tienda_id'] ?? 0);
    $nombre     = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '') ?: null;
    $precio     = (float) ($_POST['precio'] ?? 0);
    $stock      = max(0, (int) ($_POST['stock'] ?? 0));
    $badge      = trim($_POST['badge'] ?? '') ?: null;
    $estado     = in_array($_POST['estado'] ?? '', ['publicado','oculto','agotado','borrador'], true)
                  ? $_POST['estado'] : 'borrador';
    $esPreventa = !empty($_POST['es_preventa']) ? 1 : 0;
    $imagen     = trim($_POST['imagen_principal'] ?? '') ?: null;

    if ($tiendaId <= 0 || $nombre === '') {
        flash('error', 'Tienda y nombre son obligatorios.');
        redirect('/admin/productos.php?accion=nuevo');
    }

    // Límite de productos activos por tienda
    if ($estado === 'publicado') {
        $lim = $pdo->prepare('SELECT max_productos FROM tiendas WHERE id = ?');
        $lim->execute([$tiendaId]);
        $max = (int) $lim->fetchColumn();

        $cnt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE tienda_id = ? AND estado = 'publicado' AND id != ?");
        $cnt->execute([$tiendaId, $editId]);
        if ((int)$cnt->fetchColumn() >= $max) {
            flash('error', "Esta tienda ya alcanzó el máximo de {$max} productos publicados.");
            redirect('/admin/productos.php?accion=' . ($editId ? 'editar&id=' . $editId : 'nuevo'));
        }
    }

    if ($editId > 0) {
        $pdo->prepare(
            'UPDATE productos SET
              tienda_id=?, nombre=?, descripcion=?, precio=?, stock=?, stock_inicial=GREATEST(stock_inicial, ?),
              badge=?, imagen_principal=?, estado=?, es_preventa=?,
              publicado_en = CASE WHEN ? = \'publicado\' AND publicado_en IS NULL THEN NOW() ELSE publicado_en END
             WHERE id=?'
        )->execute([
            $tiendaId, $nombre, $descripcion, $precio, $stock, $stock,
            $badge, $imagen, $estado, $esPreventa, $estado, $editId
        ]);
        log_admin((int)$_SESSION['admin_id'], 'editar_producto', 'productos', $editId, $nombre);
        flash('success', 'Producto actualizado.');
    } else {
        $pdo->prepare(
            'INSERT INTO productos (
              tienda_id, nombre, descripcion, precio, stock, stock_inicial,
              badge, imagen_principal, estado, es_preventa, publicado_en
            ) VALUES (?,?,?,?,?,?,?,?,?,?, CASE WHEN ? = \'publicado\' THEN NOW() ELSE NULL END)'
        )->execute([
            $tiendaId, $nombre, $descripcion, $precio, $stock, $stock,
            $badge, $imagen, $estado, $esPreventa, $estado
        ]);
        $newId = (int) $pdo->lastInsertId();
        log_admin((int)$_SESSION['admin_id'], 'crear_producto', 'productos', $newId, $nombre);
        flash('success', 'Producto creado.');
    }
    redirect('/admin/productos.php' . ($tiendaId ? '?tienda_id=' . $tiendaId : ''));
}

$producto = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
    $stmt->execute([$id]);
    $producto = $stmt->fetch();
    if ($producto) $accion = 'editar';
}

$tiendas = $pdo->query('SELECT id, nombre_comercial, slug FROM tiendas ORDER BY es_principal DESC, nombre_comercial')->fetchAll();

$sql = 'SELECT p.*, t.nombre_comercial AS tienda_nombre
        FROM productos p
        JOIN tiendas t ON t.id = p.tienda_id';
$params = [];
if ($filtroTienda > 0) {
    $sql .= ' WHERE p.tienda_id = ?';
    $params[] = $filtroTienda;
}
$sql .= ' ORDER BY p.actualizado_en DESC LIMIT 200';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$lista = $stmt->fetchAll();

require __DIR__ . '/_layout_top.php';

if ($accion === 'nuevo' || $accion === 'editar'):
?>
  <h1><?= $accion === 'editar' ? 'Editar producto' : 'Nuevo producto' ?></h1>
  <p class="page-desc"><a href="/admin/productos.php">← Volver</a></p>
  <form class="admin-form" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)($producto['id'] ?? 0) ?>">

    <label>Tienda *
      <select name="tienda_id" required>
        <option value="">— Elegir —</option>
        <?php foreach ($tiendas as $t): ?>
          <option value="<?= (int)$t['id'] ?>" <?= ((int)($producto['tienda_id'] ?? $filtroTienda) === (int)$t['id']) ? 'selected' : '' ?>>
            <?= e($t['nombre_comercial']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Nombre *
      <input type="text" name="nombre" required value="<?= e($producto['nombre'] ?? '') ?>">
    </label>

    <label>Descripción
      <textarea name="descripcion" rows="3"><?= e($producto['descripcion'] ?? '') ?></textarea>
    </label>

    <div class="row">
      <label>Precio (MXN)
        <input type="number" name="precio" step="0.01" min="0" value="<?= e((string)($producto['precio'] ?? '0')) ?>">
      </label>
      <label>Stock
        <input type="number" name="stock" min="0" value="<?= (int)($producto['stock'] ?? 0) ?>">
      </label>
    </div>

    <div class="row">
      <label>Badge
        <input type="text" name="badge" placeholder="Vende conmigo, preventa, oferta..." value="<?= e($producto['badge'] ?? '') ?>">
      </label>
      <label>Estado
        <select name="estado">
          <?php foreach (['borrador','publicado','oculto','agotado'] as $e): ?>
            <option value="<?= $e ?>" <?= (($producto['estado'] ?? 'borrador') === $e) ? 'selected' : '' ?>><?= $e ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>

    <label>Ruta imagen principal (ej. imgs/foto.jpg o uploads/tiendas/2/foto.jpg)
      <input type="text" name="imagen_principal" value="<?= e($producto['imagen_principal'] ?? '') ?>">
    </label>

    <label style="flex-direction:row;align-items:center;gap:8px;color:var(--text)">
      <input type="checkbox" name="es_preventa" value="1" <?= !empty($producto['es_preventa']) ? 'checked' : '' ?>>
      Es preventa
    </label>

    <div class="form-actions">
      <button type="submit" class="btn">Guardar</button>
      <a class="btn btn-secondary" href="/admin/productos.php">Cancelar</a>
    </div>
  </form>
<?php else: ?>
  <h1>Productos</h1>
  <p class="page-desc">Catálogo de todas las tiendas (tú cargas todo)</p>
  <div class="toolbar">
    <form method="get" style="display:flex;gap:8px;align-items:center">
      <select name="tienda_id" onchange="this.form.submit()">
        <option value="0">Todas las tiendas</option>
        <?php foreach ($tiendas as $t): ?>
          <option value="<?= (int)$t['id'] ?>" <?= $filtroTienda === (int)$t['id'] ? 'selected' : '' ?>><?= e($t['nombre_comercial']) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
    <a class="btn" href="/admin/productos.php?accion=nuevo<?= $filtroTienda ? '&tienda_id='.$filtroTienda : '' ?>">+ Nuevo producto</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Tienda</th>
          <th>Precio</th>
          <th>Stock</th>
          <th>Badge</th>
          <th>Estado</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$lista): ?>
          <tr><td colspan="7" style="color:var(--muted)">Sin productos en base de datos aún. La tienda principal sigue usando productos.json.</td></tr>
        <?php else: foreach ($lista as $p): ?>
          <tr>
            <td><?= e($p['nombre']) ?></td>
            <td><?= e($p['tienda_nombre']) ?></td>
            <td><?= money_mx($p['precio']) ?></td>
            <td><?= (int)$p['stock'] ?></td>
            <td><?= e($p['badge'] ?? '—') ?></td>
            <td><span class="badge badge-<?= e($p['estado']) ?>"><?= e($p['estado']) ?></span></td>
            <td><a href="/admin/productos.php?id=<?= (int)$p['id'] ?>">Editar</a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
<?php endif;

require __DIR__ . '/_layout_bottom.php';
