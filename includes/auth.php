<?php
declare(strict_types=1);

function admin_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!admin_logged_in()) {
        redirect('/admin/login.php');
    }
}

function current_admin(): ?array
{
    if (!admin_logged_in()) {
        return null;
    }

    static $admin = null;
    if ($admin !== null) {
        return $admin;
    }

    $stmt = db()->prepare('SELECT id, email, nombre, activo FROM admin_usuarios WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $_SESSION['admin_id']]);
    $admin = $stmt->fetch() ?: null;

    if (!$admin || !(int) $admin['activo']) {
        logout_admin();
        return null;
    }

    return $admin;
}

function attempt_login(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT id, email, nombre, password_hash, activo FROM admin_usuarios WHERE email = ? LIMIT 1');
    $stmt->execute([trim(strtolower($email))]);
    $user = $stmt->fetch();

    if (!$user || !(int) $user['activo']) {
        return false;
    }

    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $user['id'];
    $_SESSION['admin_nombre'] = $user['nombre'];

    db()->prepare('UPDATE admin_usuarios SET ultimo_login = NOW() WHERE id = ?')
       ->execute([(int) $user['id']]);

    log_admin((int) $user['id'], 'login', 'admin_usuarios', (int) $user['id']);

    return true;
}

function logout_admin(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', $p['secure'], $p['httponly']);
    }
    session_destroy();
}
