<?php
/**
 * Tienda pública de un vendedor
 * URL: /tiendas/{slug}/  o  /tiendas/?slug=xxx
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

$slug = trim($_GET['slug'] ?? '');

// Soporte path info / rewrite: /tiendas/ricardo
if ($slug === '' && !empty($_SERVER['REQUEST_URI'])) {
    if (preg_match('#/tiendas/([a-z0-9\-]+)/?#i', $_SERVER['REQUEST_URI'], $m)) {
        $slug = strtolower($m[1]);
    }
}

if ($slug === '' || $slug === 'principal') {
    http_response_code(404);
    echo 'Tienda no encontrada';
    exit;
}

$stmt = db()->prepare(
    "SELECT * FROM tiendas WHERE slug = ? AND es_principal = 0 LIMIT 1"
);
$stmt->execute([$slug]);
$tienda = $stmt->fetch();

if (!$tienda) {
    http_response_code(404);
    echo 'Tienda no encontrada';
    exit;
}

if ($tienda['estado'] !== 'activa') {
    http_response_code(403);
    $msg = $tienda['estado'] === 'suspendida'
        ? 'Esta tienda está temporalmente suspendida.'
        : 'Esta tienda aún no está disponible.';
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>No disponible</title></head><body style="font-family:system-ui;text-align:center;padding:60px">';
    echo '<h1>' . htmlspecialchars($tienda['nombre_comercial']) . '</h1>';
    echo '<p>' . htmlspecialchars($msg) . '</p>';
    echo '<p><a href="https://losbrujosdelcine.com/">Volver a Mi Tesoro MX</a></p></body></html>';
    exit;
}

$ps = db()->prepare(
    "SELECT id, nombre, descripcion, precio, stock, badge, imagen_principal, es_preventa
     FROM productos WHERE tienda_id = ? AND estado = 'publicado' ORDER BY orden, nombre"
);
$ps->execute([(int)$tienda['id']]);
$productos = $ps->fetchAll();

$colorP = $tienda['color_principal'] ?: '#1a1a2e';
$colorS = $tienda['color_secundario'] ?: '#e94560';
$colorF = $tienda['color_fondo'] ?: '#ffffff';
$font   = $tienda['tipografia'] ?: 'system-ui';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($tienda['nombre_comercial']) ?> · Mi Tesoro MX</title>
  <style>
    :root {
      --c-primary: <?= e($colorP) ?>;
      --c-secondary: <?= e($colorS) ?>;
      --c-bg: <?= e($colorF) ?>;
      --font: <?= e($font) ?>, system-ui, sans-serif;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: var(--font);
      background: var(--c-bg);
      color: #1a1a1a;
      line-height: 1.45;
    }
    header {
      background: var(--c-primary);
      color: #fff;
      padding: 20px 16px;
      text-align: center;
    }
    header h1 { margin: 0 0 6px; font-size: 1.5rem; }
    header p { margin: 0; opacity: .85; font-size: .95rem; }
    .badge-plataforma {
      display: inline-block;
      margin-top: 10px;
      font-size: .72rem;
      background: rgba(255,255,255,.15);
      padding: 4px 10px;
      border-radius: 999px;
    }
    .wrap { max-width: 1100px; margin: 0 auto; padding: 24px 16px 80px; }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 18px;
    }
    .card {
      background: #fff;
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 0 2px 12px rgba(0,0,0,.08);
      display: flex;
      flex-direction: column;
      position: relative;
    }
    .card img {
      width: 100%;
      aspect-ratio: 1;
      object-fit: cover;
      background: #f0f0f0;
    }
    .card .info { padding: 12px 14px 16px; flex: 1; display: flex; flex-direction: column; gap: 6px; }
    .card h2 { margin: 0; font-size: 1rem; }
    .card .precio { font-weight: 700; color: var(--c-secondary); font-size: 1.1rem; }
    .card .badge {
      position: absolute; top: 10px; left: 10px;
      background: var(--c-secondary); color: #fff;
      font-size: .7rem; font-weight: 600;
      padding: 4px 8px; border-radius: 6px;
    }
    .card button {
      margin-top: auto;
      background: var(--c-primary);
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 10px;
      font: inherit;
      font-weight: 600;
      cursor: pointer;
    }
    .card button:disabled { opacity: .5; cursor: default; }
    .cart-bar {
      position: fixed; bottom: 0; left: 0; right: 0;
      background: var(--c-primary); color: #fff;
      padding: 12px 16px;
      display: flex; justify-content: space-between; align-items: center;
      gap: 12px;
      box-shadow: 0 -4px 20px rgba(0,0,0,.2);
    }
    .cart-bar button {
      background: var(--c-secondary);
      color: #fff; border: none; border-radius: 8px;
      padding: 10px 16px; font-weight: 700; cursor: pointer;
    }
    .empty { text-align: center; padding: 40px; opacity: .7; }
    footer { text-align: center; padding: 20px; font-size: .8rem; opacity: .6; }
  </style>
</head>
<body>
  <header>
    <?php if (!empty($tienda['logo_ruta'])): ?>
      <img src="/<?= e($tienda['logo_ruta']) ?>" alt="" style="max-height:56px;margin-bottom:8px">
    <?php endif; ?>
    <h1><?= e($tienda['nombre_comercial']) ?></h1>
    <?php if ($tienda['descripcion_corta']): ?>
      <p><?= e($tienda['descripcion_corta']) ?></p>
    <?php endif; ?>
    <div class="badge-plataforma">Parte de Mi Tesoro MX | Tu Tienda</div>
  </header>

  <div class="wrap">
    <?php if (!$productos): ?>
      <p class="empty">Esta tienda aún no tiene productos publicados.</p>
    <?php else: ?>
      <div class="grid" id="catalogo">
        <?php foreach ($productos as $p): ?>
          <article class="card" data-id="<?= (int)$p['id'] ?>" data-nombre="<?= e($p['nombre']) ?>" data-precio="<?= e((string)$p['precio']) ?>" data-stock="<?= (int)$p['stock'] ?>">
            <?php if ($p['badge']): ?><span class="badge"><?= e($p['badge']) ?></span><?php endif; ?>
            <img src="/<?= e($p['imagen_principal'] ?: 'imgs/placeholder.png') ?>" alt="<?= e($p['nombre']) ?>" loading="lazy"
                 onerror="this.src='/imgs/placeholder.png'">
            <div class="info">
              <h2><?= e($p['nombre']) ?></h2>
              <?php if ($p['descripcion']): ?><p style="font-size:.85rem;opacity:.75;margin:0"><?= e($p['descripcion']) ?></p><?php endif; ?>
              <div class="precio">$<?= number_format((float)$p['precio'], 2) ?> MXN</div>
              <?php if ((int)$p['stock'] <= 0): ?>
                <button type="button" disabled>Agotado</button>
              <?php else: ?>
                <button type="button" class="btn-add">Agregar al carrito</button>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="cart-bar" id="cartBar" hidden>
    <span id="cartSummary">0 productos</span>
    <button type="button" id="btnPedir">Pedir por WhatsApp</button>
  </div>

  <footer>
    Tienda alojada en <a href="https://losbrujosdelcine.com/">Mi Tesoro MX</a>
  </footer>

  <script>
  (function () {
    const SLUG = <?= json_encode($slug, JSON_UNESCAPED_UNICODE) ?>;
    const KEY = 'carrito_tienda_' + SLUG;
    let carrito = [];
    try { carrito = JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { carrito = []; }

    const bar = document.getElementById('cartBar');
    const summary = document.getElementById('cartSummary');

    function save() {
      localStorage.setItem(KEY, JSON.stringify(carrito));
      renderBar();
    }

    function renderBar() {
      const n = carrito.reduce((a, i) => a + i.cantidad, 0);
      const total = carrito.reduce((a, i) => a + i.precio * i.cantidad, 0);
      if (n === 0) { bar.hidden = true; return; }
      bar.hidden = false;
      summary.textContent = n + (n === 1 ? ' producto' : ' productos') + ' · $' + total.toFixed(2) + ' MXN';
    }

    document.querySelectorAll('.btn-add').forEach(btn => {
      btn.addEventListener('click', () => {
        const card = btn.closest('.card');
        const id = Number(card.dataset.id);
        const stock = Number(card.dataset.stock);
        const nombre = card.dataset.nombre;
        const precio = Number(card.dataset.precio);
        const found = carrito.find(x => x.id === id);
        if (found) {
          if (found.cantidad >= stock) return;
          found.cantidad++;
        } else {
          carrito.push({ id, nombre, precio, cantidad: 1 });
        }
        save();
        btn.textContent = 'En carrito';
      });
    });

    document.getElementById('btnPedir').addEventListener('click', async () => {
      if (!carrito.length) return;
      const btn = document.getElementById('btnPedir');
      btn.disabled = true;
      btn.textContent = 'Procesando…';
      try {
        const res = await fetch('/api/pedido.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            slug: SLUG,
            items: carrito.map(i => ({ id: i.id, cantidad: i.cantidad })),
            tipo_pago: 'Pago total'
          })
        });
        const data = await res.json();
        if (!data.ok) {
          alert(data.error || 'No se pudo crear el pedido');
          btn.disabled = false;
          btn.textContent = 'Pedir por WhatsApp';
          return;
        }
        carrito = [];
        save();
        window.location.href = data.whatsapp;
      } catch (e) {
        alert('Error de conexión. Intenta de nuevo.');
        btn.disabled = false;
        btn.textContent = 'Pedir por WhatsApp';
      }
    });

    renderBar();
  })();
  </script>
</body>
</html>
