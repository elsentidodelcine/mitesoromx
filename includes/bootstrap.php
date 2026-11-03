<?php
/**
 * Bootstrap común: sesión, helpers, seguridad básica.
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly'  => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

// Zona horaria México
date_default_timezone_set('America/Mexico_City');

// Errores: no mostrar en producción
ini_set('display_errors', '0');
error_reporting(E_ALL);
