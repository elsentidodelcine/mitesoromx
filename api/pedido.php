<?php
/**
 * Registrar pedido + devolver datos para WhatsApp
 * POST JSON: { slug, items:[{id, cantidad}], nombre, telefono, cp, tipo_pago }
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
    json_response(['error' => 'Método no permitido'], 405);
}

$input = json_decode(file_get_contents('php://input') ?: '{}', true);
if (!is_array($input)) {
    json_response(['error' => 'JSON inválido'], 400);
}

$slug = trim($input['slug'] ?? '');
$items = $input['items'] ?? [];
$nombre = trim($input['nombre'] ?? '') ?: null;
$telefono = trim($input['telefono'] ?? '') ?: null;
$cp = trim($input['cp'] ?? '') ?: null;
$tipoPago = trim($input['tipo_pago'] ?? 'Pago total');

if ($slug === '' || !is_array($items) || count($items) === 0) {
    json_response(['error' => 'Datos incompletos'], 400);
}

$pdo = db();
$stmt = $pdo->prepare(
    "SELECT id, nombre_comercial, whatsapp, estado FROM tiendas WHERE slug = ? AND es_principal = 0 LIMIT 1"
);
$stmt->execute([$slug]);
$tienda = $stmt->fetch();

if (!$tienda || $tienda['estado'] !== 'activa') {
    json_response(['error' => 'Tienda no disponible'], 403);
}

$tiendaId = (int) $tienda['id'];
$lineas = [];
$subtotal = 0.0;

foreach ($items as $it) {
    $pid = (int) ($it['id'] ?? 0);
    $qty = max(1, (int) ($it['cantidad'] ?? 1));
    if ($pid <= 0) continue;

    $ps = $pdo->prepare(
        "SELECT id, nombre, precio, stock, estado FROM productos
         WHERE id = ? AND tienda_id = ? AND estado = 'publicado' LIMIT 1"
    );
    $ps->execute([$pid, $tiendaId]);
    $prod = $ps->fetch();
    if (!$prod) {
        json_response(['error' => 'Producto no válido en esta tienda'], 400);
    }
    if ((int)$prod['stock'] < $qty) {
        json_response(['error' => 'Stock insuficiente: ' . $prod['nombre']], 409);
    }

    $precio = (float) $prod['precio'];
    $sub = $precio * $qty;
    $subtotal += $sub;
    $lineas[] = [
        'producto_id' => (int) $prod['id'],
        'nombre'      => $prod['nombre'],
        'precio'      => $precio,
        'cantidad'    => $qty,
        'subtotal'    => $sub,
    ];
}

if (!$lineas) {
    json_response(['error' => 'Sin productos válidos'], 400);
}

$total = $subtotal; // envío lo define el vendedor por WhatsApp
$codigo = 'MT-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

try {
    $pdo->beginTransaction();

    $pdo->prepare(
        'INSERT INTO pedidos (tienda_id, codigo, nombre_cliente, telefono, codigo_postal, tipo_pago, subtotal, total, estado)
         VALUES (?,?,?,?,?,?,?,?,\'pendiente\')'
    )->execute([$tiendaId, $codigo, $nombre, $telefono, $cp, $tipoPago, $subtotal, $total]);

    $pedidoId = (int) $pdo->lastInsertId();

    $ins = $pdo->prepare(
        'INSERT INTO pedido_items (pedido_id, producto_id, nombre_producto, precio_unitario, cantidad, subtotal)
         VALUES (?,?,?,?,?,?)'
    );
    foreach ($lineas as $l) {
        $ins->execute([$pedidoId, $l['producto_id'], $l['nombre'], $l['precio'], $l['cantidad'], $l['subtotal']]);
        // Reducir stock
        $pdo->prepare('UPDATE productos SET stock = GREATEST(0, stock - ?) WHERE id = ? AND tienda_id = ?')
            ->execute([$l['cantidad'], $l['producto_id'], $tiendaId]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    json_response(['error' => 'No se pudo registrar el pedido'], 500);
}

// Mensaje WhatsApp
$msg = "Hola, soy cliente de *{$tienda['nombre_comercial']}* (Mi Tesoro MX | Tu Tienda).\n\n";
$msg .= "Pedido: *{$codigo}*\n";
$msg .= "━━━━━━━━━━━━━━━━\n*PRODUCTOS*\n";
foreach ($lineas as $l) {
    $msg .= "• {$l['nombre']}\n  {$l['cantidad']} x $" . number_format($l['precio'], 2) . " = $" . number_format($l['subtotal'], 2) . "\n";
}
$msg .= "━━━━━━━━━━━━━━━━\n";
$msg .= "Subtotal: $" . number_format($subtotal, 2) . " MXN\n";
$msg .= "*TOTAL: $" . number_format($total, 2) . " MXN*\n";
$msg .= "Tipo de pago: {$tipoPago}\n";
if ($cp) $msg .= "CP: {$cp}\n";
if ($nombre) $msg .= "Nombre: {$nombre}\n";
$msg .= "\nQuedo atento(a) para confirmar envío. ¡Gracias!";

$wa = preg_replace('/\D+/', '', $tienda['whatsapp']);
$waUrl = 'https://wa.me/' . $wa . '?text=' . rawurlencode($msg);

json_response([
    'ok'         => true,
    'codigo'     => $codigo,
    'total'      => $total,
    'whatsapp'   => $waUrl,
    'tienda'     => $tienda['nombre_comercial'],
]);
