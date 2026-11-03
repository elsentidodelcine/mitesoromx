<?php
declare(strict_types=1);
$admin = current_admin();
$current = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'Admin') ?> · Mi Tesoro MX</title>
  <link rel="stylesheet" href="/admin/assets/admin.css">
</head>
<body>
<div class="admin-wrap">
  <aside class="sidebar">
    <div class="brand">Mi Tesoro MX</div>
    <nav>
      <a href="/admin/" class="<?= $current === 'index.php' ? 'active' : '' ?>">Dashboard</a>
      <a href="/admin/tiendas.php" class="<?= $current === 'tiendas.php' ? 'active' : '' ?>">Tiendas</a>
      <a href="/admin/vendedores.php" class="<?= $current === 'vendedores.php' ? 'active' : '' ?>">Vendedores</a>
      <a href="/admin/productos.php" class="<?= $current === 'productos.php' ? 'active' : '' ?>">Productos</a>
      <a href="/admin/suscripciones.php" class="<?= $current === 'suscripciones.php' ? 'active' : '' ?>">Suscripciones</a>
      <a href="/admin/pagos.php" class="<?= $current === 'pagos.php' ? 'active' : '' ?>">Pagos</a>
      <a href="/admin/logout.php">Cerrar sesión</a>
    </nav>
    <p style="margin-top:24px;padding:0 8px;font-size:.75rem;color:var(--muted)">
      <?= e($admin['nombre'] ?? '') ?>
    </p>
  </aside>
  <main class="main">
    <?php
    $flash = get_flash();
    if ($flash):
    ?>
      <div class="alert alert-<?= e($flash['type'] === 'error' ? 'error' : ($flash['type'] === 'warning' ? 'warning' : 'success')) ?>">
        <?= e($flash['message']) ?>
      </div>
    <?php endif; ?>
