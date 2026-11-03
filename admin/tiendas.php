<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$pageTitle = 'Tiendas';
$pdo = db();
$accion = $_GET['accion'] ?? 'lista';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Guardar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        flash('error', 'Token inválido.');
        redirect('/admin/tiendas.php');
    }

    $editId           = (int) ($_POST['id'] ?? 0);
    $vendedorId       = (int) ($_POST['vendedor_id'] ?? 0) ?: null;
    $nombre           = trim($_POST['nombre_comercial'] ?? '');
    $slug             = slugify(trim($_POST['slug'] ?? $nombre));
    $descripcion      = trim($_POST['descripcion_corta'] ?? '') ?: null;
    $whatsapp         = preg_replace('/\D+/', '', $_POST['whatsapp'] ?? '') ?: '';
    $colorPrincipal   = trim($_POST['color_principal'] ?? '#1a1a2e');
    $colorSecundario  = trim($_POST['color_secundario'] ?? '#e94560');
    $colorFondo       = trim($_POST['color_fondo'] ?? '#ffffff');
    $tipografia       = trim($_POST['tipografia'] ?? 'system-ui');
    $maxProductos     = max(1, min(500, (int) ($_POST['max_productos'] ?? 50)));
    $estado           = in_array($_POST['estado'] ?? '', ['activa','suspendida','borrador'], true)
                        ? $_POST['estado'] : 'borrador';
    $fechaActivacion  = $_POST['fecha_activacion'] ?: null;
    $fechaVencimiento = $_POST['fecha_vencimiento'] ?: null;

    if ($nombre === '' || $whatsapp === '') {
        flash('error', 'Nombre comercial y WhatsApp son obligatorios.');
        redirect('/admin/tiendas.php?accion=' . ($editId ? 'editar&id=' . $editId : 'nueva'));
    }

    // Slug único
    $check = $pdo->prepare('SELECT id FROM tiendas WHERE slug = ? AND id != ?');
    $check->execute([$slug, $editId]);
    if ($check->fetch()) {
        $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
    }

    if ($editId > 0) {
        $pdo->prepare(
            'UPDATE tiendas SET
              vendedor_id=?, nombre_comercial=?, slug=?, descripcion_corta=?,
              whatsapp=?, color_principal=?, color_secundario=?, color_fondo=?,
              tipografia=?, max_productos=?, estado=?, fecha_activacion=?, fecha_vencimiento=?
             WHERE id=? AND es_principal = 0'
        )->execute([
            $vendedorId, $nombre, $slug, $descripcion,
            $whatsapp, $colorPrincipal, $colorSecundario, $colorFondo,
            $tipografia, $maxProductos, $estado, $fechaActivacion, $fechaVencimiento,
            $editId
        ]);
        log_admin((int)$_SESSION['admin_id'], 'editar_tienda', 'tiendas', $editId, $nombre);
        flash('success', 'Tienda actualizada.');
    } else {
        $pdo->prepare(
            'INSERT INTO tiendas (
              vendedor_id, nombre_comercial, slug, descripcion_corta, whatsapp,
              color_principal, color_secundario, color_fondo, tipografia,
              max_productos, es_principal, estado, fecha_activacion, fecha_vencimiento
            ) VALUES (?,?,?,?,?,?,?,?,?,?,0,?,?,?)'
        )->execute([
            $vendedorId, $nombre, $slug, $descripcion, $whatsapp,
            $colorPrincipal, $colorSecundario, $colorFondo, $tipografia,
            $maxProductos, $estado, $fechaActivacion, $fechaVencimiento
        ]);
        $newId = (int) $pdo->lastInsertId();

        // Suscripción inicial si está activa y tiene vencimiento
        if ($estado === 'activa' && $fechaActivacion && $fechaVencimiento) {
            $pdo->prepare(
                'INSERT INTO suscripciones (tienda_id, precio_mensual, fecha_inicio, fecha_vencimiento, estado)
                 VALUES (?, 300.00, ?, ?, ?)'
            )->execute([$newId, $fechaActivacion, $fechaVencimiento, 'activa']);
        }

        log_admin((int)$_SESSION['admin_id'], 'crear_tienda', 'tiendas', $newId, $nombre);
        flash('success', 'Tienda creada. URL: /tiendas/' . $slug);
    }
    redirect('/admin/tiendas.php');
}

$tienda = null;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM tiendas WHERE id = ?');
    $stmt->execute([$id]);
    $tienda = $stmt->fetch();
    if ($tienda) {
        $accion = 'editar';
    }
}
if ($accion === 'nueva') {
    $tienda = null;
}

$vendedores = $pdo->query("SELECT id, nombre FROM vendedores WHERE estado = 'activo' ORDER BY nombre")->fetchAll();
$lista = $pdo->query(
    "SELECT t.*, v.nombre AS vendedor_nombre,
      (SELECT COUNT(*) FROM productos p WHERE p.tienda_id = t.id) AS total_productos
     FROM tiendas t
     LEFT JOIN vendedores v ON v.id = t.vendedor_id
     ORDER BY t.es_principal DESC, t.nombre_comercial"
)->fetchAll();

require __DIR__ . '/_layout_top.php';

if ($accion === 'nueva' || $accion === 'editar'):
  $esPrincipal = $tienda && (int)$tienda['es_principal'] === 1;
?>
  <h1><?= $accion === 'editar' ? 'Editar tienda' : 'Nueva tienda' ?></h1>
  <p class="page-desc"><a href="/admin/tiendas.php">← Volver</a>
    <?php if ($tienda && !$esPrincipal): ?>
      · Pública: <a href="/tiendas/<?= e($tienda['slug']) ?>" target="_blank">/tiendas/<?= e($tienda['slug']) ?></a>
    <?php endif; ?>
  </p>

  <?php if ($esPrincipal): ?>
    <div class="alert alert-warning">Esta es la tienda principal (Mi Tesoro MX). Algunos campos están limitados.</div>
  <?php endif; ?>

  <form class="admin-form" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)($tienda['id'] ?? 0) ?>">

    <label>Nombre comercial *
      <input type="text" name="nombre_comercial" required value="<?= e($tienda['nombre_comercial'] ?? '') ?>" <?= $esPrincipal ? 'readonly' : '' ?>>
    </label>

    <div class="row">
      <label>Slug (URL) *
        <input type="text" name="slug" required pattern="[a-z0-9\-]+" value="<?= e($tienda['slug'] ?? '') ?>" <?= $esPrincipal ? 'readonly' : '' ?>>
      </label>
      <label>Vendedor
        <select name="vendedor_id" <?= $esPrincipal ? 'disabled' : '' ?>>
          <option value="">— Sin asignar —</option>
          <?php foreach ($vendedores as $v): ?>
            <option value="<?= (int)$v['id'] ?>" <?= ((int)($tienda['vendedor_id'] ?? 0) === (int)$v['id']) ? 'selected' : '' ?>>
              <?= e($v['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>

    <label>Descripción corta
      <input type="text" name="descripcion_corta" maxlength="300" value="<?= e($tienda['descripcion_corta'] ?? '') ?>">
    </label>

    <label>WhatsApp (solo números, ej. 524761002824) *
      <input type="text" name="whatsapp" required value="<?= e($tienda['whatsapp'] ?? '') ?>">
    </label>

    <div class="row">
      <label>Color principal
        <input type="color" name="color_principal" value="<?= e($tienda['color_principal'] ?? '#1a1a2e') ?>">
      </label>
      <label>Color secundario
        <input type="color" name="color_secundario" value="<?= e($tienda['color_secundario'] ?? '#e94560') ?>">
      </label>
    </div>
    <div class="row">
      <label>Color de fondo
        <input type="color" name="color_fondo" value="<?= e($tienda['color_fondo'] ?? '#ffffff') ?>">
      </label>
      <label>Tipografía
        <input type="text" name="tipografia" value="<?= e($tienda['tipografia'] ?? 'system-ui') ?>">
      </label>
    </div>

    <div class="row">
      <label>Máx. productos
        <input type="number" name="max_productos" min="1" max="9999" value="<?= (int)($tienda['max_productos'] ?? 50) ?>" <?= $esPrincipal ? 'readonly' : '' ?>>
      </label>
      <label>Estado
        <select name="estado" <?= $esPrincipal ? 'disabled' : '' ?>>
          <?php foreach (['borrador','activa','suspendida'] as $e): ?>
            <option value="<?= $e ?>" <?= (($tienda['estado'] ?? 'borrador') === $e) ? 'selected' : '' ?>><?= $e ?></option>
          <?php endforeach; ?>
        </select>
        <?php if ($esPrincipal): ?><input type="hidden" name="estado" value="activa"><?php endif; ?>
      </label>
    </div>

    <div class="row">
      <label>Fecha activación
        <input type="date" name="fecha_activacion" value="<?= e($tienda['fecha_activacion'] ?? '') ?>">
      </label>
      <label>Fecha vencimiento
        <input type="date" name="fecha_vencimiento" value="<?= e($tienda['fecha_vencimiento'] ?? '') ?>">
      </label>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn">Guardar</button>
      <a class="btn btn-secondary" href="/admin/tiendas.php">Cancelar</a>
    </div>
  </form>
<?php else: ?>
  <h1>Tiendas</h1>
  <p class="page-desc">Tiendas de la plataforma (incluida la principal)</p>
  <div class="toolbar">
    <span></span>
    <a class="btn" href="/admin/tiendas.php?accion=nueva">+ Nueva tienda</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Slug</th>
          <th>Vendedor</th>
          <th>Productos</th>
          <th>Estado</th>
          <th>Vence</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($lista as $t): ?>
          <tr>
            <td>
              <?= e($t['nombre_comercial']) ?>
              <?php if ((int)$t['es_principal']): ?><span class="badge badge-activa">principal</span><?php endif; ?>
            </td>
            <td><code><?= e($t['slug']) ?></code></td>
            <td><?= e($t['vendedor_nombre'] ?? '—') ?></td>
            <td><?= (int)$t['total_productos'] ?> / <?= (int)$t['max_productos'] ?></td>
            <td><span class="badge badge-<?= e($t['estado']) ?>"><?= e($t['estado']) ?></span></td>
            <td><?= e($t['fecha_vencimiento'] ?? '—') ?></td>
            <td>
              <a href="/admin/tiendas.php?id=<?= (int)$t['id'] ?>">Editar</a>
              <?php if (!(int)$t['es_principal'] && $t['estado'] === 'activa'): ?>
                · <a href="/tiendas/<?= e($t['slug']) ?>" target="_blank">Ver</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif;

require __DIR__ . '/_layout_bottom.php';
