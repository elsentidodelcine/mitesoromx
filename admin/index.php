<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();

$pageTitle = 'Dashboard';

$pdo = db();

$totalTiendas     = (int) $pdo->query("SELECT COUNT(*) FROM tiendas WHERE es_principal = 0")->fetchColumn();
$tiendasActivas   = (int) $pdo->query("SELECT COUNT(*) FROM tiendas WHERE estado = 'activa' AND es_principal = 0")->fetchColumn();
$tiendasSusp      = (int) $pdo->query("SELECT COUNT(*) FROM tiendas WHERE estado = 'suspendida'")->fetchColumn();
$proximasVencer   = (int) $pdo->query("SELECT COUNT(*) FROM tiendas WHERE estado = 'activa' AND es_principal = 0 AND fecha_vencimiento IS NOT NULL AND fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();
$totalProductos   = (int) $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
$ingresosMes      = (float) $pdo->query("SELECT COALESCE(SUM(monto),0) FROM pagos WHERE YEAR(creado_en) = YEAR(CURDATE()) AND MONTH(creado_en) = MONTH(CURDATE())")->fetchColumn();
$proyectado       = $tiendasActivas * 300;

$tiendasRecientes = $pdo->query(
    "SELECT t.id, t.nombre_comercial, t.slug, t.estado, t.fecha_vencimiento, v.nombre AS vendedor
     FROM tiendas t
     LEFT JOIN vendedores v ON v.id = t.vendedor_id
     WHERE t.es_principal = 0
     ORDER BY t.creado_en DESC
     LIMIT 8"
)->fetchAll();

require __DIR__ . '/_layout_top.php';
?>

<h1>Dashboard</h1>
<p class="page-desc">Resumen de la plataforma Mi Tesoro MX | Tu Tienda</p>

<div class="stats">
  <div class="stat-card">
    <div class="label">Tiendas (terceros)</div>
    <div class="value"><?= $totalTiendas ?></div>
  </div>
  <div class="stat-card">
    <div class="label">Activas</div>
    <div class="value"><?= $tiendasActivas ?></div>
  </div>
  <div class="stat-card">
    <div class="label">Suspendidas</div>
    <div class="value"><?= $tiendasSusp ?></div>
  </div>
  <div class="stat-card">
    <div class="label">Por vencer (7 días)</div>
    <div class="value"><?= $proximasVencer ?></div>
  </div>
  <div class="stat-card">
    <div class="label">Productos totales</div>
    <div class="value"><?= $totalProductos ?></div>
  </div>
  <div class="stat-card">
    <div class="label">Ingresos este mes</div>
    <div class="value" style="font-size:1.2rem"><?= money_mx($ingresosMes) ?></div>
  </div>
  <div class="stat-card">
    <div class="label">Proyectado mensual</div>
    <div class="value" style="font-size:1.2rem"><?= money_mx($proyectado) ?></div>
  </div>
</div>

<div class="toolbar">
  <h2 style="margin:0;font-size:1.1rem">Tiendas recientes</h2>
  <a class="btn btn-sm" href="/admin/tiendas.php?accion=nueva">+ Nueva tienda</a>
</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Slug</th>
        <th>Vendedor</th>
        <th>Estado</th>
        <th>Vence</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$tiendasRecientes): ?>
        <tr><td colspan="6" style="color:var(--muted)">Aún no hay tiendas de terceros.</td></tr>
      <?php else: foreach ($tiendasRecientes as $t): ?>
        <tr>
          <td><?= e($t['nombre_comercial']) ?></td>
          <td><code><?= e($t['slug']) ?></code></td>
          <td><?= e($t['vendedor'] ?? '—') ?></td>
          <td><span class="badge badge-<?= e($t['estado']) ?>"><?= e($t['estado']) ?></span></td>
          <td><?= e($t['fecha_vencimiento'] ?? '—') ?></td>
          <td><a href="/admin/tiendas.php?id=<?= (int)$t['id'] ?>">Editar</a></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
