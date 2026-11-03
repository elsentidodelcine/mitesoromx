<?php
/**
 * API pública: datos de una tienda + productos publicados
 * GET /api/tienda.php?slug=ricardo
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

header('Access-Control-Allow-Origin: *');

$slug = trim($_GET['slug'] ?? '');
if ($slug === '' || $slug === 'principal') {
    json_response(['error' => 'Slug inválido'], 400);
}

$stmt = db()->prepare(
    "SELECT id, nombre_comercial, slug, descripcion_corta, logo_ruta, banner_ruta,
            whatsapp, color_principal, color_secundario, color_fondo, tipografia,
            estilo_tarjetas, estado
     FROM tiendas
     WHERE slug = ? AND es_principal = 0
     LIMIT 1"
);
$stmt->execute([$slug]);
$tienda = $stmt->fetch();

if (!$tienda) {
    json_response(['error' => 'Tienda no encontrada'], 404);
}

if ($tienda['estado'] !== 'activa') {
    json_response(['error' => 'Tienda no disponible', 'estado' => $tienda['estado']], 403);
}

$ps = db()->prepare(
    "SELECT id, nombre, descripcion, precio, stock, badge, imagen_principal, es_preventa
     FROM productos
     WHERE tienda_id = ? AND estado = 'publicado'
     ORDER BY orden ASC, nombre ASC"
);
$ps->execute([(int) $tienda['id']]);
$productos = $ps->fetchAll();

// No exponer id interno de más si no hace falta; sí lo dejamos para carrito/validación
json_response([
    'tienda'    => $tienda,
    'productos' => $productos,
]);
