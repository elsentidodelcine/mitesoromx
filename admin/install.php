<?php
/**
 * Instalador de un solo uso: crea el primer administrador.
 * BÓRRALO después de usarlo.
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

$pdo = db();
$existe = (int) $pdo->query('SELECT COUNT(*) FROM admin_usuarios')->fetchColumn();

$done = false;
$error = '';

if ($existe > 0) {
    $error = 'Ya existe al menos un administrador. Elimina este archivo install.php.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email  = trim(strtolower($_POST['email'] ?? ''));
    $pass   = $_POST['password'] ?? '';
    $pass2  = $_POST['password2'] ?? '';

    if ($nombre === '' || $email === '' || $pass === '') {
        $error = 'Completa todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Correo no válido.';
    } elseif (strlen($pass) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($pass !== $pass2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO admin_usuarios (email, password_hash, nombre, activo) VALUES (?, ?, ?, 1)'
        );
        $stmt->execute([$email, $hash, $nombre]);
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Instalar admin · Mi Tesoro MX</title>
  <link rel="stylesheet" href="/admin/assets/admin.css">
</head>
<body class="login-page">
  <div class="login-card">
    <h1>Crear administrador</h1>
    <p class="subtitle">Solo se usa una vez. Borra install.php después.</p>

    <?php if ($done): ?>
      <div class="alert alert-success">
        Administrador creado. <a href="/admin/login.php">Ir al login</a>.
        <br><strong>Elimina /admin/install.php ahora.</strong>
      </div>
    <?php elseif ($error && $existe > 0): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
      <a class="btn" href="/admin/login.php">Ir al login</a>
    <?php else: ?>
      <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
      <form method="post">
        <label>Nombre
          <input type="text" name="nombre" required value="<?= e($_POST['nombre'] ?? '') ?>">
        </label>
        <label>Correo
          <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
        </label>
        <label>Contraseña (mín. 8)
          <input type="password" name="password" required minlength="8">
        </label>
        <label>Repetir contraseña
          <input type="password" name="password2" required minlength="8">
        </label>
        <button type="submit" style="margin-top:10px;width:100%">Crear administrador</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
