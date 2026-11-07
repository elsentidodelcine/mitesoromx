<?php
/**
 * Recibe reportes / quejas sobre tiendas independientes
 * POST application/x-www-form-urlencoded o JSON
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (str_contains($contentType, 'application/json')) {
    $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?: [];
} else {
    $input = $_POST;
}

// Honeypot anti-bots
if (!empty($input['website'])) {
    json_response(['ok' => true]); // silencio
}

$tipo = $input['tipo'] ?? 'queja';
if (!in_array($tipo, ['queja', 'practica_indebida', 'otro'], true)) {
    $tipo = 'queja';
}

$nombre = trim((string) ($input['nombre'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));
$telefono = trim((string) ($input['telefono'] ?? ''));
$mensaje = trim((string) ($input['mensaje'] ?? ''));
$slug = trim((string) ($input['tienda_slug'] ?? ''));
$tiendaNombre = trim((string) ($input['tienda_nombre'] ?? ''));

if ($mensaje === '' || mb_strlen($mensaje) < 15) {
    json_response(['ok' => false, 'error' => 'Describe el problema con al menos 15 caracteres.'], 400);
}

if (mb_strlen($mensaje) > 4000) {
    json_response(['ok' => false, 'error' => 'El mensaje es demasiado largo.'], 400);
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['ok' => false, 'error' => 'Correo no válido.'], 400);
}

// Rate limit simple por IP (máx. 5 por hora)
$ip = $_SERVER['REMOTE_ADDR'] ?? null;
try {
    if ($ip) {
        $rl = db()->prepare(
            "SELECT COUNT(*) FROM reportes_tiendas
             WHERE ip = ? AND creado_en >= (NOW() - INTERVAL 1 HOUR)"
        );
        $rl->execute([$ip]);
        if ((int) $rl->fetchColumn() >= 5) {
            json_response(['ok' => false, 'error' => 'Demasiados reportes. Intenta más tarde.'], 429);
        }
    }
} catch (Throwable $e) {
    // si la tabla no existe aún, fallará abajo
}

$tiendaId = null;
if ($slug !== '') {
    try {
        $st = db()->prepare(
            "SELECT id, nombre_comercial FROM tiendas WHERE slug = ? AND es_principal = 0 LIMIT 1"
        );
        $st->execute([$slug]);
        $row = $st->fetch();
        if ($row) {
            $tiendaId = (int) $row['id'];
            if ($tiendaNombre === '') {
                $tiendaNombre = $row['nombre_comercial'];
            }
        }
    } catch (Throwable $e) {
        // continuar sin id
    }
}

try {
    $ins = db()->prepare(
        "INSERT INTO reportes_tiendas
          (tienda_id, tienda_slug, tienda_nombre, tipo, nombre_reporta, email, telefono, mensaje, estado, ip)
         VALUES (?,?,?,?,?,?,?,?, 'nuevo', ?)"
    );
    $ins->execute([
        $tiendaId,
        $slug !== '' ? $slug : null,
        $tiendaNombre !== '' ? $tiendaNombre : null,
        $tipo,
        $nombre !== '' ? $nombre : null,
        $email !== '' ? $email : null,
        $telefono !== '' ? $telefono : null,
        $mensaje,
        $ip,
    ]);
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => 'No se pudo guardar el reporte. ¿Existe la tabla reportes_tiendas?'], 500);
}

json_response(['ok' => true, 'message' => 'Reporte enviado. Gracias, lo revisaremos.']);
