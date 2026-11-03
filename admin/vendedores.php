<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$pageTitle = 'Vendedores';
$pdo = db();
$accion = $_GET['accion'] ?? 'lista';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Guardar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        flash('error', 'Token inválido.');
        redirect('/admin/vendedores.php');
    }

    $nombre   = trim($_POST['nombre'] ?? '');
    $email    = trim($_POST['email'] ?? '') ?: null;
    $telefono = trim($_POST['telefono'] ?? '') ?: null;
    $whatsapp = trim($_POST['whatsapp'] ?? '') ?: null;
    $notas    = trim($_POST['notas'] ?? '') ?: null;
    $estado   = ($_POST['estado'] ?? 'activo') === 'inactivo' ? 'inactivo' : 'activo';
    $editId   = (int) ($_POST['id'] ?? 0);

    if ($nombre === '') {
        flash('error', 'El nombre es obligatorio.');
        redirect('/admin/vendedores.php?accion=' . ($editId ? 'editar&id=' . $editId : 'nuevo'));
    }

    if ($editId > 0) {
        $pdo->prepare(
            'UPDATE vendedores SET nombre=?, email=?, telefono=?, whatsapp=?, notas=?, estado=? WHERE id=?'
        )->execute([$nombre, $email, $telefono, $whatsapp, $notas, $estado, $editId]);
        log_admin((int)$_SESSION['admin_id'], 'editar_vendedor', 'vendedores', $editId, $nombre);
        flash('success', 'Vendedor actualizado.');
    } else {
        $pdo->prepare(
            'INSERT INTO vendedores (nombre, email, telefono, whatsapp, notas, estado) VALUES (?,?,?,?,?,?)'
        )->execute([$nombre, $email, $telefono, $whatsapp, $notas, $estado]);
        $newId = (int) $pdo->lastInsertId();
        log_admin((int)$_SESSION['admin_id'], 'crear_vendedor', 'vendedores', $newId, $nombre);
        flash('success', 'Vendedor creado.');
    }
    redirect('/admin/vendedores.php');
}

$vendedor = null;
if (($accion === 'editar' || $accion === 'nuevo') && $id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM vendedores WHERE id = ?');
    $stmt->execute([$id]);
    $vendedor = $stmt->fetch();
    if (!$vendedor) {
        flash('error', 'Vendedor no encontrado.');
        redirect('/admin/vendedores.php');
    }
    $accion = 'editar';
}

$lista = $pdo->query('SELECT * FROM vendedores ORDER BY nombre')->fetchAll();

require __DIR__ . '/_layout_top.php';

if ($accion === 'nuevo' || $accion === 'editar'):
?>
  <h1><?= $accion === 'editar' ? 'Editar vendedor' : 'Nuevo vendedor' ?></h1>
  <p class="page-desc"><a href="/admin/vendedores.php">← Volver</a></p>
  <form class="admin-form" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)($vendedor['id'] ?? 0) ?>">
    <label>Nombre *
      <input type="text" name="nombre" required value="<?= e($vendedor['nombre'] ?? '') ?>">
    </label>
    <div class="row">
      <label>Correo
        <input type="email" name="email" value="<?= e($vendedor['email'] ?? '') ?>">
      </label>
      <label>Teléfono
        <input type="text" name="telefono" value="<?= e($vendedor['telefono'] ?? '') ?>">
      </label>
    </div>
    <label>WhatsApp (solo números, con lada país)
      <input type="text" name="whatsapp" placeholder="524761002824" value="<?= e($vendedor['whatsapp'] ?? '') ?>">
    </label>
    <label>Notas
      <textarea name="notas" rows="3"><?= e($vendedor['notas'] ?? '') ?></textarea>
    </label>
    <label>Estado
      <select name="estado">
        <option value="activo" <?= (($vendedor['estado'] ?? '') === 'activo') ? 'selected' : '' ?>>Activo</option>
        <option value="inactivo" <?= (($vendedor['estado'] ?? '') === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
      </select>
    </label>
    <div class="form-actions">
      <button type="submit" class="btn">Guardar</button>
      <a class="btn btn-secondary" href="/admin/vendedores.php">Cancelar</a>
    </div>
  </form>
<?php else: ?>
  <h1>Vendedores</h1>
  <p class="page-desc">Personas dueñas de tiendas de terceros</p>
  <div class="toolbar">
    <span></span>
    <a class="btn" href="/admin/vendedores.php?accion=nuevo">+ Nuevo vendedor</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Nombre</th>
          <th>WhatsApp</th>
          <th>Correo</th>
          <th>Estado</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$lista): ?>
          <tr><td colspan="5" style="color:var(--muted)">Sin vendedores aún.</td></tr>
        <?php else: foreach ($lista as $v): ?>
          <tr>
            <td><?= e($v['nombre']) ?></td>
            <td><?= e($v['whatsapp'] ?? '—') ?></td>
            <td><?= e($v['email'] ?? '—') ?></td>
            <td><span class="badge badge-<?= e($v['estado']) ?>"><?= e($v['estado']) ?></span></td>
            <td><a href="/admin/vendedores.php?accion=editar&id=<?= (int)$v['id'] ?>">Editar</a></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
<?php endif;

require __DIR__ . '/_layout_bottom.php';
