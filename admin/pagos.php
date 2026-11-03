<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$pageTitle = 'Pagos';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        flash('error', 'Token inválido.');
        redirect('/admin/pagos.php');
    }

    $tiendaId = (int) ($_POST['tienda_id'] ?? 0);
    $monto    = (float) ($_POST['monto'] ?? 300);
    $metodo   = trim($_POST['metodo'] ?? '') ?: null;
    $ref      = trim($_POST['referencia'] ?? '') ?: null;
    $notas    = trim($_POST['notas'] ?? '') ?: null;
    $inicio   = $_POST['periodo_inicio'] ?: null;
    $fin      = $_POST['periodo_fin'] ?: null;
    $extender = !empty($_POST['extender_tienda']);

    if ($tiendaId <= 0) {
        flash('error', 'Selecciona una tienda.');
        redirect('/admin/pagos.php');
    }

    $pdo->prepare(
        'INSERT INTO pagos (tienda_id, monto, metodo, referencia, periodo_inicio, periodo_fin, notas, registrado_por)
         VALUES (?,?,?,?,?,?,?,?)'
    )->execute([
        $tiendaId, $monto, $metodo, $ref, $inicio, $fin, $notas, (int)$_SESSION['admin_id']
    ]);

    if ($extender && $fin) {
        $pdo->prepare(
            "UPDATE tiendas SET estado = 'activa', fecha_vencimiento = ? WHERE id = ? AND es_principal = 0"
        )->execute([$fin, $tiendaId]);

        $pdo->prepare(
            "UPDATE suscripciones SET estado = 'activa', fecha_vencimiento = ? WHERE tienda_id = ? ORDER BY id DESC LIMIT 1"
        );
        // MariaDB may not allow ORDER BY in UPDATE easily; do explicit last suscripcion
        $sid = $pdo->prepare('SELECT id FROM suscripciones WHERE tienda_id = ? ORDER BY id DESC LIMIT 1');
        $sid->execute([$tiendaId]);
        $susId = $sid->fetchColumn();
        if ($susId) {
            $pdo->prepare("UPDATE suscripciones SET estado='activa', fecha_vencimiento=? WHERE id=?")
                ->execute([$fin, $susId]);
        } else {
            $pdo->prepare(
                'INSERT INTO suscripciones (tienda_id, precio_mensual, fecha_inicio, fecha_vencimiento, estado)
                 VALUES (?, ?, COALESCE(?, CURDATE()), ?, \'activa\')'
            )->execute([$tiendaId, $monto, $inicio, $fin]);
        }
    }

    log_admin((int)$_SESSION['admin_id'], 'registrar_pago', 'tiendas', $tiendaId, "Monto: $monto");
    flash('success', 'Pago registrado' . ($extender ? ' y tienda extendida/reactivada.' : '.'));
    redirect('/admin/pagos.php');
}

$tiendas = $pdo->query("SELECT id, nombre_comercial FROM tiendas WHERE es_principal = 0 ORDER BY nombre_comercial")->fetchAll();
$lista = $pdo->query(
    "SELECT p.*, t.nombre_comercial
     FROM pagos p
     JOIN tiendas t ON t.id = p.tienda_id
     ORDER BY p.creado_en DESC
     LIMIT 100"
)->fetchAll();

require __DIR__ . '/_layout_top.php';
?>
<h1>Pagos</h1>
<p class="page-desc">Registro manual de mensualidades</p>

<form class="admin-form" method="post" style="margin-bottom:28px">
  <?= csrf_field() ?>
  <h2 style="margin:0;font-size:1.05rem">Registrar pago</h2>
  <label>Tienda *
    <select name="tienda_id" required>
      <option value="">— Elegir —</option>
      <?php foreach ($tiendas as $t): ?>
        <option value="<?= (int)$t['id'] ?>"><?= e($t['nombre_comercial']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <div class="row">
    <label>Monto
      <input type="number" name="monto" step="0.01" value="300">
    </label>
    <label>Método
      <input type="text" name="metodo" placeholder="Transferencia, OXXO...">
    </label>
  </div>
  <label>Referencia
    <input type="text" name="referencia">
  </label>
  <div class="row">
    <label>Periodo inicio
      <input type="date" name="periodo_inicio" value="<?= date('Y-m-d') ?>">
    </label>
    <label>Periodo fin (nuevo vencimiento)
      <input type="date" name="periodo_fin" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
    </label>
  </div>
  <label>Notas
    <textarea name="notas" rows="2"></textarea>
  </label>
  <label style="flex-direction:row;align-items:center;gap:8px;color:var(--text)">
    <input type="checkbox" name="extender_tienda" value="1" checked>
    Reactivar tienda y actualizar fecha de vencimiento
  </label>
  <div class="form-actions">
    <button type="submit" class="btn">Registrar pago</button>
  </div>
</form>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Tienda</th>
        <th>Monto</th>
        <th>Método</th>
        <th>Periodo</th>
        <th>Ref.</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$lista): ?>
        <tr><td colspan="6" style="color:var(--muted)">Sin pagos registrados.</td></tr>
      <?php else: foreach ($lista as $p): ?>
        <tr>
          <td><?= e(substr($p['creado_en'], 0, 16)) ?></td>
          <td><?= e($p['nombre_comercial']) ?></td>
          <td><?= money_mx($p['monto']) ?></td>
          <td><?= e($p['metodo'] ?? '—') ?></td>
          <td><?= e(($p['periodo_inicio'] ?? '') . ' → ' . ($p['periodo_fin'] ?? '')) ?></td>
          <td><?= e($p['referencia'] ?? '—') ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/_layout_bottom.php'; ?>
