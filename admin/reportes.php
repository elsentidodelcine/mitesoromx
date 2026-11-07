<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$pageTitle = 'Reportes';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        flash('error', 'Token inválido.');
        redirect('/admin/reportes.php');
    }
    $id = (int) ($_POST['id'] ?? 0);
    $estado = $_POST['estado'] ?? '';
    $notas = trim($_POST['notas_admin'] ?? '') ?: null;
    if ($id > 0 && in_array($estado, ['nuevo', 'en_revision', 'cerrado'], true)) {
        $pdo->prepare('UPDATE reportes_tiendas SET estado = ?, notas_admin = ? WHERE id = ?')
            ->execute([$estado, $notas, $id]);
        flash('success', 'Reporte actualizado.');
    }
    redirect('/admin/reportes.php');
}

$lista = [];
try {
    $lista = $pdo->query(
        "SELECT * FROM reportes_tiendas ORDER BY creado_en DESC LIMIT 100"
    )->fetchAll();
} catch (Throwable $e) {
    $lista = [];
    $dbError = $e->getMessage();
}

require __DIR__ . '/_layout_top.php';
?>
<h1>Reportes de tiendas</h1>
<p class="page-desc">Quejas y reportes enviados desde el formulario público</p>

<?php if (!empty($dbError)): ?>
  <div class="alert alert-error">
    No se pudo leer la tabla <code>reportes_tiendas</code>. Ejecuta el SQL de creación.
    <br><small><?= e($dbError) ?></small>
  </div>
<?php endif; ?>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Tipo</th>
        <th>Tienda</th>
        <th>Contacto</th>
        <th>Mensaje</th>
        <th>Estado</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$lista): ?>
        <tr><td colspan="7" style="color:var(--muted)">Sin reportes aún.</td></tr>
      <?php else: foreach ($lista as $r): ?>
        <tr>
          <td style="white-space:nowrap"><?= e(substr((string)$r['creado_en'], 0, 16)) ?></td>
          <td><?= e($r['tipo']) ?></td>
          <td>
            <?= e($r['tienda_nombre'] ?: ($r['tienda_slug'] ?: '—')) ?>
            <?php if (!empty($r['tienda_slug'])): ?>
              <br><a href="/tiendas/<?= e($r['tienda_slug']) ?>" target="_blank"><code><?= e($r['tienda_slug']) ?></code></a>
            <?php endif; ?>
          </td>
          <td style="font-size:.85rem">
            <?= e($r['nombre_reporta'] ?: '—') ?><br>
            <?= e($r['email'] ?: '') ?><br>
            <?= e($r['telefono'] ?: '') ?>
          </td>
          <td style="max-width:280px;font-size:.85rem"><?= e(mb_strimwidth($r['mensaje'], 0, 180, '…')) ?></td>
          <td><span class="badge badge-<?= e($r['estado'] === 'cerrado' ? 'oculto' : ($r['estado'] === 'nuevo' ? 'borrador' : 'publicado')) ?>"><?= e($r['estado']) ?></span></td>
          <td>
            <details>
              <summary>Gestionar</summary>
              <form method="post" style="margin-top:8px;display:flex;flex-direction:column;gap:6px;min-width:200px">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <select name="estado">
                  <?php foreach (['nuevo','en_revision','cerrado'] as $e): ?>
                    <option value="<?= $e ?>" <?= $r['estado'] === $e ? 'selected' : '' ?>><?= $e ?></option>
                  <?php endforeach; ?>
                </select>
                <textarea name="notas_admin" rows="2" placeholder="Notas internas"><?= e($r['notas_admin'] ?? '') ?></textarea>
                <button type="submit" class="btn btn-sm">Guardar</button>
              </form>
              <p style="font-size:.8rem;opacity:.8;margin-top:8px"><?= nl2br(e($r['mensaje'])) ?></p>
            </details>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/_layout_bottom.php'; ?>
