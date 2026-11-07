<?php
/**
 * Listado público de tiendas activas (terceros)
 * GET /api/tiendas.php
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

$stmt = db()->query(
    "SELECT
        id,
        nombre_comercial,
        slug,
        descripcion_corta,
        logo_ruta,
        banner_ruta,
        color_principal,
        color_secundario
     FROM tiendas
     WHERE es_principal = 0
       AND estado = 'activa'
       AND (fecha_vencimiento IS NULL OR fecha_vencimiento >= CURDATE())
     ORDER BY nombre_comercial ASC"
);

$tiendas = $stmt->fetchAll();

json_response([
    'ok'      => true,
    'total'   => count($tiendas),
    'tiendas' => $tiendas,
]);
