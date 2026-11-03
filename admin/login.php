<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

if (admin_logged_in()) {
    redirect('/admin/');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Token de seguridad inválido. Recarga la página.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        if ($email === '' || $pass === '') {
            $error = 'Completa correo y contraseña.';
        } elseif (attempt_login($email, $pass)) {
            redirect('/admin/');
        } else {
            $error = 'Credenciales incorrectas.';
            usleep(300000); // anti fuerza bruta leve
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin · Mi Tesoro MX</title>
  <link rel="stylesheet" href="/admin/assets/admin.css">
</head>
<body class="login-page">
  <form class="login-card" method="post" autocomplete="off">
    <h1>Mi Tesoro MX</h1>
    <p class="subtitle">Panel de administración</p>
    <?php if ($error): ?>
      <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>
    <?= csrf_field() ?>
    <label>Correo
      <input type="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>">
    </label>
    <label>Contraseña
      <input type="password" name="password" required>
    </label>
    <button type="submit">Entrar</button>
  </form>
</body>
</html>
