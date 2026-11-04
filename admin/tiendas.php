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
    $headerTagline    = trim($_POST['header_tagline'] ?? '') ?: 'Tienda independiente';
    $footerTexto      = trim($_POST['footer_texto'] ?? '') ?: null;
    $enviosTexto      = trim($_POST['envios_texto'] ?? '') ?: null;
    $terminosTexto    = trim($_POST['terminos_texto'] ?? '') ?: null;
    $facebookUrl      = trim($_POST['facebook_url'] ?? '') ?: null;
    $instagramUrl     = trim($_POST['instagram_url'] ?? '') ?: null;
    $menuExtraLabel   = trim($_POST['menu_extra_label'] ?? '') ?: null;
    $menuExtraTexto   = trim($_POST['menu_extra_texto'] ?? '') ?: null;
    $mostrarRed       = !empty($_POST['mostrar_red']) ? 1 : 0;
    $logoRuta         = trim($_POST['logo_ruta'] ?? '') ?: null;
    $bannerRuta       = trim($_POST['banner_ruta'] ?? '') ?: null;
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


    // Subida de logo (opcional)
    if (!empty($_FILES['logo_file']['tmp_name']) && is_uploaded_file($_FILES['logo_file']['tmp_name'])) {
        $file = $_FILES['logo_file'];
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (isset($allowed[$mime]) && $file['size'] <= 2 * 1024 * 1024) {
            $dir = dirname(__DIR__) . '/uploads/tiendas';
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $subdir = $dir . '/' . ($editId > 0 ? $editId : 'tmp');
            if (!is_dir($subdir)) {
                @mkdir($subdir, 0755, true);
            }
            $name = 'logo_' . time() . '.' . $allowed[$mime];
            $dest = $subdir . '/' . $name;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $logoRuta = 'uploads/tiendas/' . ($editId > 0 ? $editId : 'tmp') . '/' . $name;
            }
        }
    }

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
              header_tagline=?, footer_texto=?, envios_texto=?, terminos_texto=?,
              facebook_url=?, instagram_url=?, menu_extra_label=?, menu_extra_texto=?,
              mostrar_red=?, logo_ruta=?, banner_ruta=?,
              whatsapp=?, color_principal=?, color_secundario=?, color_fondo=?,
              tipografia=?, max_productos=?, estado=?, fecha_activacion=?, fecha_vencimiento=?
             WHERE id=? AND es_principal = 0'
        )->execute([
            $vendedorId, $nombre, $slug, $descripcion,
            $headerTagline, $footerTexto, $enviosTexto, $terminosTexto,
            $facebookUrl, $instagramUrl, $menuExtraLabel, $menuExtraTexto,
            $mostrarRed, $logoRuta, $bannerRuta,
            $whatsapp, $colorPrincipal, $colorSecundario, $colorFondo,
            $tipografia, $maxProductos, $estado, $fechaActivacion, $fechaVencimiento,
            $editId
        ]);
        log_admin((int)$_SESSION['admin_id'], 'editar_tienda', 'tiendas', $editId, $nombre);
        flash('success', 'Tienda actualizada.');
    } else {
        $pdo->prepare(
            'INSERT INTO tiendas (
              vendedor_id, nombre_comercial, slug, descripcion_corta,
              header_tagline, footer_texto, envios_texto, terminos_texto,
              facebook_url, instagram_url, menu_extra_label, menu_extra_texto,
              mostrar_red, logo_ruta, banner_ruta, whatsapp,
              color_principal, color_secundario, color_fondo, tipografia,
              max_productos, es_principal, estado, fecha_activacion, fecha_vencimiento
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0,?,?,?)'
        )->execute([
            $vendedorId, $nombre, $slug, $descripcion,
            $headerTagline, $footerTexto, $enviosTexto, $terminosTexto,
            $facebookUrl, $instagramUrl, $menuExtraLabel, $menuExtraTexto,
            $mostrarRed, $logoRuta, $bannerRuta, $whatsapp,
            $colorPrincipal, $colorSecundario, $colorFondo, $tipografia,
            $maxProductos, $estado, $fechaActivacion, $fechaVencimiento
        ]);
        $newId = (int) $pdo->lastInsertId();

        // Si el logo se subió a tmp, moverlo a la carpeta de la tienda
        if ($logoRuta && str_contains($logoRuta, '/tmp/')) {
            $src = dirname(__DIR__) . '/' . $logoRuta;
            $newRel = 'uploads/tiendas/' . $newId . '/' . basename($logoRuta);
            $dstDir = dirname(__DIR__) . '/uploads/tiendas/' . $newId;
            if (!is_dir($dstDir)) @mkdir($dstDir, 0755, true);
            $dst = dirname(__DIR__) . '/' . $newRel;
            if (is_file($src) && @rename($src, $dst)) {
                $pdo->prepare('UPDATE tiendas SET logo_ruta = ? WHERE id = ?')->execute([$newRel, $newId]);
            }
        }

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

  <form class="admin-form" method="post" enctype="multipart/form-data">
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

    <label>Descripción corta (hero y footer)
      <input type="text" name="descripcion_corta" maxlength="300" value="<?= e($tienda['descripcion_corta'] ?? '') ?>">
    </label>

    <label>Texto bajo el nombre en el header
      <input type="text" name="header_tagline" maxlength="120" placeholder="Tienda independiente"
             value="<?= e($tienda['header_tagline'] ?? 'Tienda independiente') ?>">
    </label>

    <label>Texto del footer
      <textarea name="footer_texto" rows="3" placeholder="Los pedidos se confirman por WhatsApp con esta tienda."><?= e($tienda['footer_texto'] ?? '') ?></textarea>
    </label>

    <label>Condiciones de envío (menú hamburguesa + footer)
      <textarea name="envios_texto" rows="4" placeholder="Envíos a todo México..."><?= e($tienda['envios_texto'] ?? '') ?></textarea>
    </label>

    <label>Términos y condiciones (menú + footer)
      <textarea name="terminos_texto" rows="4" placeholder="Términos de esta tienda..."><?= e($tienda['terminos_texto'] ?? '') ?></textarea>
    </label>

    <div class="row">
      <label>Facebook (URL)
        <input type="url" name="facebook_url" placeholder="https://facebook.com/..." value="<?= e($tienda['facebook_url'] ?? '') ?>">
      </label>
      <label>Instagram (URL)
        <input type="url" name="instagram_url" placeholder="https://instagram.com/..." value="<?= e($tienda['instagram_url'] ?? '') ?>">
      </label>
    </div>

    <label>Pestaña extra del menú (nombre)
      <input type="text" name="menu_extra_label" maxlength="80" placeholder="Ej. Sobre nosotros" value="<?= e($tienda['menu_extra_label'] ?? '') ?>">
    </label>
    <label>Pestaña extra (contenido)
      <textarea name="menu_extra_texto" rows="3"><?= e($tienda['menu_extra_texto'] ?? '') ?></textarea>
    </label>

    <label style="flex-direction:row;align-items:center;gap:8px;color:var(--text)">
      <input type="checkbox" name="mostrar_red" value="1" <?= !isset($tienda['mostrar_red']) || (int)($tienda['mostrar_red'] ?? 1) === 1 ? 'checked' : '' ?>>
      Mostrar “Alojado en Mi Tesoro MX | Tu Tienda” en el footer
    </label>

    <label>Logo (subir imagen JPG/PNG/WebP, máx. 2 MB)
      <input type="file" name="logo_file" accept="image/jpeg,image/png,image/webp,image/gif">
    </label>
    <?php if (!empty($tienda['logo_ruta'])): ?>
      <p style="margin:0;font-size:.85rem;color:var(--muted)">
        Logo actual: <code><?= e($tienda['logo_ruta']) ?></code><br>
        <img src="/<?= e($tienda['logo_ruta']) ?>" alt="" style="max-height:64px;margin-top:8px;border-radius:8px">
      </p>
    <?php endif; ?>
    <label>Ruta del logo (manual, opcional)
      <input type="text" name="logo_ruta" placeholder="uploads/tiendas/2/logo.jpg" value="<?= e($tienda['logo_ruta'] ?? '') ?>">
    </label>
    <label>Ruta del banner (opcional)
      <input type="text" name="banner_ruta" placeholder="uploads/tiendas/2/banner.jpg" value="<?= e($tienda['banner_ruta'] ?? '') ?>">
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
