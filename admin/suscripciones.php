<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$pageTitle = 'Suscripciones';
$pdo = db();

$lista = $pdo->query(
    "SELECT s.*, t.nombre_comercial, t.slug, t.estado AS tienda_estado
     FROM suscripciones s
     JOIN tiendas t ON t.id = s.tienda_id
     ORDER BY s.fecha_vencimiento ASC"
)->fetchAll();

require __DIR__ . '/_layout_top.php';
?>
<h1>Suscripciones</h1>
<p class="page-desc">Ciclos de facturación ($300 MXN / mes)</p>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Tienda</th>
        <th>Inicio</th>
        <th>Vence</th>
        <th>Precio</th>
        <th>Estado suscripción</th>
        <th>Estado tienda</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$lista): ?>
        <tr><td colspan="6" style="color:var(--muted)">Sin suscripciones registradas.</td></tr>
      <?php else: foreach ($lista as $s): ?>
        <tr>
          <td><?= e($s['nombre_comercial']) ?></td>
          <td><?= e($s['fecha_inicio']) ?></td>
          <td><?= e($s['fecha_vencimiento']) ?></td>
          <td><?= money_mx($s['precio_mensual']) ?></td>
          <td><span class="badge badge-<?= e($s['estado']) ?>"><?= e($s['estado']) ?></span></td>
          <td><span class="badge badge-<?= e($s['tienda_estado']) ?>"><?= e($s['tienda_estado']) ?></span></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/_layout_bottom.php'; ?>
