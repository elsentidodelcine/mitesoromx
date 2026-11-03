<?php
/**
 * Tienda pública de un vendedor — estilo Mi Tesoro MX
 * URL: /tiendas/{slug}
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

$slug = trim($_GET['slug'] ?? '');

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
    ?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>No disponible | Mi Tesoro MX</title>
  <link rel="stylesheet" href="/styles.css">
</head>
<body>
  <header class="main-header">
    <div class="header-left">
      <a href="/" style="display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit">
        <img src="/imgs/logo.png" alt="Mi Tesoro MX" class="site-logo">
        <div class="logo-text"><h1>Mi Tesoro MX</h1><span>Los Brujos del Cine</span></div>
      </a>
    </div>
  </header>
  <main style="text-align:center;padding:60px 16px">
    <h2><?= e($tienda['nombre_comercial']) ?></h2>
    <p><?= e($msg) ?></p>
    <p><a class="boton" href="/tiendas.html">Ver otras tiendas</a></p>
  </main>
</body>
</html><?php
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
$wa = preg_replace('/\D+/', '', $tienda['whatsapp'] ?? '');
$nombreTienda = $tienda['nombre_comercial'];
$descCorta = $tienda['descripcion_corta'] ?: 'Coleccionables y piezas seleccionadas.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($nombreTienda) ?> | Mi Tesoro MX</title>
  <meta name="description" content="<?= e($descCorta) ?>">
  <link rel="icon" href="/imgs/logo.png" type="image/png" sizes="32x32">
  <link rel="stylesheet" href="/styles.css">
  <link rel="stylesheet" href="/Estructura/footer.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --tienda-primary: <?= e($colorP) ?>;
      --tienda-secondary: <?= e($colorS) ?>;
    }

    .tienda-page main {
      max-width: 1100px;
      margin: 0 auto;
      padding: 0 16px 48px;
    }

    .tienda-hero-wrap {
      position: relative;
      margin: 12px auto 8px;
      max-width: 820px;
      padding: 0 16px;
    }
    .tienda-hero {
      text-align: center;
      padding: 32px 22px 28px;
      border-radius: 20px;
      background:
        radial-gradient(120% 80% at 50% 0%, color-mix(in srgb, var(--tienda-secondary) 22%, transparent), transparent 55%),
        linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,0));
      border: 1px solid color-mix(in srgb, var(--tienda-secondary) 35%, rgba(255,255,255,.08));
      box-shadow: 0 10px 40px rgba(0,0,0,.18);
    }
    body:not(.dark) .tienda-hero {
      background:
        radial-gradient(120% 80% at 50% 0%, color-mix(in srgb, var(--tienda-secondary) 16%, transparent), transparent 55%),
        linear-gradient(180deg, #fff, #f7f7f9);
      border: 1px solid color-mix(in srgb, var(--tienda-secondary) 28%, rgba(0,0,0,.08));
      box-shadow: 0 10px 28px rgba(0,0,0,.08);
    }
    .tienda-hero-kicker {
      display: inline-block;
      margin: 0 0 12px;
      font-size: .72rem;
      font-weight: 700;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: var(--tienda-secondary);
      opacity: .95;
    }
    .tienda-hero h2 {
      margin: 0 0 10px;
      font-size: clamp(1.75rem, 5vw, 2.35rem);
      letter-spacing: -0.03em;
      line-height: 1.15;
      font-weight: 700;
    }
    .tienda-hero p {
      margin: 0 auto;
      max-width: 42ch;
      opacity: .82;
      font-size: 1.05rem;
      line-height: 1.5;
    }
    .tienda-badge-red {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin: 16px 0 0;
      font-size: .74rem;
      font-weight: 700;
      letter-spacing: .04em;
      text-transform: uppercase;
      padding: 8px 14px;
      border-radius: 999px;
      color: #fff;
      background: linear-gradient(135deg, var(--tienda-primary), var(--tienda-secondary));
      border: none;
      box-shadow: 0 6px 16px color-mix(in srgb, var(--tienda-secondary) 35%, transparent);
    }

    .tienda-page .catalogo-title {
      text-align: center;
      margin: 34px auto 4px;
      padding: 0 16px;
    }
    .tienda-page .catalogo-title h2 {
      margin: 0 0 6px;
      font-size: 1.15rem;
      font-weight: 700;
      letter-spacing: .04em;
      text-transform: uppercase;
      opacity: .9;
    }
    .tienda-page .catalogo-title p {
      margin: 0;
      opacity: .65;
      font-size: .9rem;
    }
    .tienda-page .catalogo-title::after {
      content: "";
      display: block;
      width: 48px;
      height: 3px;
      margin: 14px auto 0;
      border-radius: 999px;
      background: linear-gradient(90deg, var(--tienda-primary), var(--tienda-secondary));
    }

    /* Grid de productos más profesional */
    .tienda-page #catalogo {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 20px;
      justify-content: center;
      padding: 16px 0 24px;
    }
    /* Si hay pocos productos, no se estiran a todo el ancho */
    .tienda-page #catalogo .producto {
      max-width: 280px;
      width: 100%;
      margin: 0 auto;
      border-radius: 16px;
      overflow: hidden;
      transition: transform .18s ease, box-shadow .18s ease;
    }
    .tienda-page #catalogo .producto:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 28px rgba(0,0,0,.14);
    }
    body.dark .tienda-page #catalogo .producto:hover {
      box-shadow: 0 12px 28px rgba(0,0,0,.45);
    }
    .tienda-page #catalogo .producto .img-wrapper {
      aspect-ratio: 1;
      overflow: hidden;
      background: rgba(0,0,0,.04);
    }
    body.dark .tienda-page #catalogo .producto .img-wrapper {
      background: rgba(255,255,255,.04);
    }
    .tienda-page #catalogo .producto .producto-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .tienda-page #catalogo .producto .info {
      padding: 12px 14px 14px;
    }
    .tienda-page #catalogo .producto .info h2 {
      font-size: .98rem;
      line-height: 1.3;
      margin: 0 0 4px;
    }
    .tienda-page #catalogo .producto .descripcion-oferta {
      font-size: .82rem;
      opacity: .7;
      margin: 0 0 6px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .tienda-page #catalogo .producto .precio {
      font-size: 1.05rem;
      font-weight: 700;
      margin: 4px 0 10px;
    }
    .tienda-page #catalogo .producto .boton {
      width: 100%;
    }

    /* Acento de color de la tienda (sutil) */
    .tienda-page .boton:not(.proximamente):not(.apartado):not(:disabled) {
      background: var(--tienda-primary);
    }
    .tienda-page .btn-whatsapp {
      background: #25d366 !important;
    }

    .sin-productos {
      text-align: center;
      padding: 56px 16px;
      opacity: .75;
      grid-column: 1 / -1;
    }

    .tienda-back {
      text-align: center;
      padding: 8px 16px 32px;
    }
    .tienda-back a {
      font-size: .9rem;
      opacity: .75;
    }
    .tienda-back a:hover { opacity: 1; }

    /* Carrito: título más claro */
    .tienda-page .cart-header h2 {
      font-size: 1rem;
    }

    /* ===== Header propio de la tienda ===== */
    .store-header {
      position: sticky;
      top: 0;
      z-index: 40;
      backdrop-filter: blur(10px);
      background: color-mix(in srgb, var(--tienda-primary) 12%, rgba(10,10,12,.92));
      border-bottom: 1px solid color-mix(in srgb, var(--tienda-secondary) 30%, rgba(255,255,255,.08));
    }
    body:not(.dark) .store-header {
      background: color-mix(in srgb, var(--tienda-primary) 6%, #fff);
      border-bottom: 1px solid color-mix(in srgb, var(--tienda-secondary) 22%, rgba(0,0,0,.08));
    }
    .store-header-inner {
      max-width: 1100px;
      margin: 0 auto;
      padding: 12px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }
    .store-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 0;
    }
    .store-logo {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      object-fit: cover;
      background: #111;
      flex-shrink: 0;
    }
    .store-logo-fallback {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      display: grid;
      place-items: center;
      font-weight: 800;
      font-size: 1.1rem;
      color: #fff;
      background: linear-gradient(135deg, var(--tienda-primary), var(--tienda-secondary));
      flex-shrink: 0;
    }
    .store-brand-text {
      display: flex;
      flex-direction: column;
      min-width: 0;
    }
    .store-brand-text strong {
      font-size: 1.02rem;
      line-height: 1.2;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .store-brand-text span {
      font-size: .75rem;
      opacity: .7;
    }
    .store-header-actions {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-shrink: 0;
    }

    /* ===== Footer propio ===== */
    .store-footer {
      margin-top: 24px;
      border-top: 1px solid rgba(255,255,255,.08);
      background: color-mix(in srgb, var(--tienda-primary) 10%, transparent);
    }
    body:not(.dark) .store-footer {
      border-top: 1px solid rgba(0,0,0,.08);
      background: color-mix(in srgb, var(--tienda-primary) 4%, #f6f6f8);
    }
    .store-footer-inner {
      max-width: 1100px;
      margin: 0 auto;
      padding: 28px 16px 36px;
      display: grid;
      gap: 16px;
    }
    @media (min-width: 700px) {
      .store-footer-inner {
        grid-template-columns: 1.2fr 1fr;
        align-items: start;
      }
    }
    .store-footer-brand strong {
      display: block;
      font-size: 1.05rem;
      margin-bottom: 6px;
    }
    .store-footer-brand p,
    .store-footer-meta p {
      margin: 0;
      opacity: .75;
      font-size: .9rem;
      line-height: 1.45;
    }
    .store-footer-meta {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .store-footer-network {
      font-size: .82rem !important;
      opacity: .65 !important;
    }
    .store-footer-network a {
      color: var(--tienda-secondary);
      text-decoration: none;
      font-weight: 600;
    }
    .store-footer-network a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body class="tienda-page">

<!-- HEADER PROPIO DE LA TIENDA -->
<header class="store-header">
  <div class="store-header-inner">
    <a class="store-brand" href="?slug=<?= e($slug) ?>" style="text-decoration:none;color:inherit">
      <?php if (!empty($tienda['logo_ruta'])): ?>
        <img src="/<?= e($tienda['logo_ruta']) ?>" alt="" class="store-logo" onerror="this.style.display='none'">
      <?php else: ?>
        <span class="store-logo-fallback" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($nombreTienda, 0, 1))) ?></span>
      <?php endif; ?>
      <div class="store-brand-text">
        <strong><?= e($nombreTienda) ?></strong>
        <span>Tienda independiente</span>
      </div>
    </a>

    <div class="store-header-actions">
      <button id="verCarrito" class="cart-btn" aria-label="Ver carrito" type="button">
        🛒 <span id="cartCount">0</span>
      </button>
      <button id="toggleTheme" class="theme-btn" aria-label="Cambiar tema" type="button">🌙</button>
    </div>
  </div>
</header>

<!-- HERO TIENDA -->
<div class="tienda-hero-wrap">
  <section class="tienda-hero">
    <div class="tienda-hero-kicker">Tienda oficial de la red</div>
    <h2><?= e($nombreTienda) ?></h2>
    <p><?= e($descCorta) ?></p>
    <div class="tienda-badge-red">Mi Tesoro MX | Tu Tienda</div>
  </section>
</div>

<div class="catalogo-title">
  <h2>Catálogo</h2>
  <p><?= count($productos) ?> producto<?= count($productos) === 1 ? '' : 's' ?> disponible<?= count($productos) === 1 ? '' : 's' ?></p>
</div>

<main>
  <section id="catalogo" aria-label="Catálogo de la tienda">
    <?php if (!$productos): ?>
      <p class="sin-productos">Esta tienda aún no tiene productos publicados.</p>
    <?php else: ?>
      <?php foreach ($productos as $p):
        $badge = trim((string)($p['badge'] ?? ''));
        $badgeClass = $badge
          ? strtolower(preg_replace('/\s+/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $badge) ?: $badge))
          : '';
        $img = $p['imagen_principal'] ?: 'imgs/placeholder.png';
        if ($img && $img[0] !== '/') {
          // rutas relativas desde raíz del sitio
        }
        $stock = (int)$p['stock'];
        $precio = (float)$p['precio'];
      ?>
        <article class="producto"
                 data-id="<?= (int)$p['id'] ?>"
                 data-nombre="<?= e($p['nombre']) ?>"
                 data-precio="<?= e((string)$precio) ?>"
                 data-stock="<?= $stock ?>"
                 data-imagen="<?= e($img) ?>">
          <?php if ($badge): ?>
            <span class="badge <?= e($badgeClass) ?>"><?= e($badge) ?></span>
          <?php endif; ?>
          <div class="img-wrapper">
            <img src="/<?= e($img) ?>"
                 alt="<?= e($p['nombre']) ?>"
                 class="producto-img"
                 loading="lazy"
                 onerror="this.onerror=null;this.src='/imgs/placeholder.png'">
          </div>
          <div class="info">
            <h2><?= e($p['nombre']) ?></h2>
            <?php if (!empty($p['descripcion'])): ?>
              <p class="descripcion-oferta"><?= e($p['descripcion']) ?></p>
            <?php endif; ?>
            <p class="precio">$<?= number_format($precio, 2) ?> MXN</p>
            <?php if ($stock === 1): ?>
              <p class="urgencia-real">🔥 ¡Última pieza disponible!</p>
            <?php elseif ($stock > 1 && $stock <= 3): ?>
              <p class="urgencia-real">⚡ Solo quedan <?= $stock ?> piezas</p>
            <?php endif; ?>
            <?php if ($stock <= 0): ?>
              <span class="sin-stock">AGOTADO</span>
            <?php else: ?>
              <button class="boton" type="button">Agregar al carrito</button>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</main>

<!-- FOOTER PROPIO DE LA TIENDA -->
<footer class="store-footer">
  <div class="store-footer-inner">
    <div class="store-footer-brand">
      <strong><?= e($nombreTienda) ?></strong>
      <p><?= e($descCorta) ?></p>
    </div>
    <div class="store-footer-meta">
      <p>Los pedidos se confirman por WhatsApp con esta tienda.</p>
      <p class="store-footer-network">
        Alojado en <a href="/tiendas.html">Mi Tesoro MX | Tu Tienda</a>
      </p>
    </div>
  </div>
</footer>

<!-- CARRITO DRAWER -->
<aside id="cartDrawer" class="cart-drawer" aria-label="Carrito de compras">
  <div class="cart-header">
    <h2>🛒 Carrito · <?= e($nombreTienda) ?></h2>
    <button id="cerrarDrawer" type="button" aria-label="Cerrar carrito">✕</button>
  </div>
  <div id="cartItems" class="cart-items"></div>
  <div class="cart-footer">
    <p id="cartTotal">Tu carrito está vacío</p>
    <button type="button" id="btnSeguirComprando" class="btn-seguir-comprando" style="display:none">
      Seguir comprando
    </button>
    <button type="button" id="btnPedirWhats" class="btn-whatsapp" style="display:none">
      Confirmar por WhatsApp
    </button>
    <button id="vaciarCarrito" class="btn-vaciar" type="button" disabled>Vaciar carrito</button>
  </div>
</aside>
<div id="cartOverlay"></div>

<!-- TOAST -->
<div id="cartToast" class="cart-toast" role="status" style="display:none">
  <strong id="toastTitle">Agregado al carrito</strong>
  <p id="toastText"></p>
  <div class="toast-actions">
    <button id="toastVerCarrito" type="button">Ver carrito</button>
    <button id="toastCerrar" type="button" aria-label="Cerrar">✕</button>
  </div>
</div>

<script>
(function () {
  const SLUG = <?= json_encode($slug, JSON_UNESCAPED_UNICODE) ?>;
  const TIENDA_NOMBRE = <?= json_encode($nombreTienda, JSON_UNESCAPED_UNICODE) ?>;
  const KEY = 'carrito_tienda_' + SLUG;

  let carrito = [];
  try { carrito = JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { carrito = []; }

  const drawer = document.getElementById('cartDrawer');
  const overlay = document.getElementById('cartOverlay');
  const cartItems = document.getElementById('cartItems');
  const cartTotal = document.getElementById('cartTotal');
  const cartCount = document.getElementById('cartCount');
  const btnPedir = document.getElementById('btnPedirWhats');
  const btnSeguir = document.getElementById('btnSeguirComprando');
  const btnVaciar = document.getElementById('vaciarCarrito');
  const toast = document.getElementById('cartToast');
  const toastTitle = document.getElementById('toastTitle');
  const toastText = document.getElementById('toastText');

  function save() {
    localStorage.setItem(KEY, JSON.stringify(carrito));
    renderCart();
    updateCount();
  }

  function updateCount() {
    const n = carrito.reduce((a, i) => a + i.cantidad, 0);
    if (cartCount) cartCount.textContent = n;
  }

  function money(n) {
    return '$' + Number(n).toLocaleString('es-MX', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' MXN';
  }

  function renderCart() {
    if (!cartItems || !cartTotal) return;

    if (carrito.length === 0) {
      cartItems.innerHTML = '';
      cartTotal.innerHTML = '<div class="cart-empty"><p class="cart-empty-title">Tu carrito está vacío</p><p class="cart-empty-sub">Agrega productos de esta tienda.</p></div>';
      if (btnPedir) btnPedir.style.display = 'none';
      if (btnSeguir) btnSeguir.style.display = 'none';
      if (btnVaciar) btnVaciar.disabled = true;
      return;
    }

    if (btnPedir) btnPedir.style.display = 'block';
    if (btnSeguir) btnSeguir.style.display = 'block';
    if (btnVaciar) btnVaciar.disabled = false;

    let subtotal = 0;
    cartItems.innerHTML = carrito.map((item, index) => {
      const sub = item.precio * item.cantidad;
      subtotal += sub;
      return `
        <div class="cart-item">
          <img src="/${item.imagen || 'imgs/placeholder.png'}" class="cart-img" alt="" onerror="this.src='/imgs/placeholder.png'">
          <div class="cart-info">
            <p class="cart-name">${escapeHtml(item.nombre)}</p>
            <p class="cart-price">${money(item.precio)}</p>
            <div class="cart-qty-row">
              <button class="cart-qty-btn" data-action="minus" data-index="${index}" type="button">−</button>
              <span class="cart-qty-num">${item.cantidad}</span>
              <button class="cart-qty-btn" data-action="plus" data-index="${index}" type="button">+</button>
            </div>
          </div>
          <button class="cart-remove" data-index="${index}" type="button" aria-label="Eliminar">✕</button>
        </div>`;
    }).join('');

    cartTotal.innerHTML = `
      <div class="cart-summary">
        <div class="cart-summary-row cart-summary-total">
          <span>Total</span>
          <span>${money(subtotal)}</span>
        </div>
        <p class="cart-summary-note">El envío lo confirma el vendedor por WhatsApp.</p>
      </div>`;

    cartItems.querySelectorAll('.cart-qty-btn').forEach(btn => {
      btn.onclick = () => {
        const i = Number(btn.dataset.index);
        const action = btn.dataset.action;
        if (!carrito[i]) return;
        if (action === 'plus') {
          if (carrito[i].cantidad < (carrito[i].stock || 99)) carrito[i].cantidad++;
        } else {
          if (carrito[i].cantidad <= 1) carrito.splice(i, 1);
          else carrito[i].cantidad--;
        }
        save();
        syncButtons();
      };
    });
    cartItems.querySelectorAll('.cart-remove').forEach(btn => {
      btn.onclick = () => {
        carrito.splice(Number(btn.dataset.index), 1);
        save();
        syncButtons();
      };
    });
  }

  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function showToast(nombre) {
    if (!toast) return;
    toastTitle.textContent = 'Agregado al carrito';
    toastText.textContent = '"' + nombre + '" se agregó al carrito';
    toast.style.display = 'block';
    toast.classList.add('show');
    clearTimeout(toast._t);
    toast._t = setTimeout(() => {
      toast.classList.remove('show');
      toast.style.display = 'none';
    }, 3200);
  }

  function openDrawer() {
    drawer?.classList.add('open');
    overlay?.classList.add('show');
  }
  function closeDrawer() {
    drawer?.classList.remove('open');
    overlay?.classList.remove('show');
  }

  function syncButtons() {
    document.querySelectorAll('.producto').forEach(card => {
      const id = Number(card.dataset.id);
      const btn = card.querySelector('.boton');
      if (!btn) return;
      const en = carrito.find(c => c.id === id);
      if (en) {
        btn.textContent = 'En carrito';
        btn.classList.add('apartado');
        btn.disabled = true;
      } else {
        btn.textContent = 'Agregar al carrito';
        btn.classList.remove('apartado');
        btn.disabled = false;
      }
    });
  }

  // Agregar al carrito
  document.querySelectorAll('.producto .boton').forEach(btn => {
    btn.addEventListener('click', () => {
      const card = btn.closest('.producto');
      const id = Number(card.dataset.id);
      const stock = Number(card.dataset.stock);
      const nombre = card.dataset.nombre;
      const precio = Number(card.dataset.precio);
      const imagen = card.dataset.imagen || '';

      const found = carrito.find(x => x.id === id);
      if (found) {
        if (found.cantidad >= stock) return;
        found.cantidad++;
      } else {
        carrito.push({ id, nombre, precio, cantidad: 1, stock, imagen });
      }
      save();
      syncButtons();
      showToast(nombre);
      card.classList.add('added');
      setTimeout(() => card.classList.remove('added'), 500);
    });
  });

  document.getElementById('verCarrito')?.addEventListener('click', openDrawer);
  document.getElementById('cerrarDrawer')?.addEventListener('click', closeDrawer);
  overlay?.addEventListener('click', closeDrawer);
  btnSeguir?.addEventListener('click', closeDrawer);

  document.getElementById('toastVerCarrito')?.addEventListener('click', () => {
    toast.style.display = 'none';
    openDrawer();
  });
  document.getElementById('toastCerrar')?.addEventListener('click', () => {
    toast.style.display = 'none';
  });

  btnVaciar?.addEventListener('click', () => {
    if (!carrito.length) return;
    if (!confirm('¿Vaciar el carrito de esta tienda?')) return;
    carrito = [];
    save();
    syncButtons();
  });

  // Pedir por WhatsApp (valida stock en servidor)
  btnPedir?.addEventListener('click', async () => {
    if (!carrito.length) return;
    btnPedir.disabled = true;
    btnPedir.textContent = 'Procesando…';
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
        btnPedir.disabled = false;
        btnPedir.textContent = 'Confirmar por WhatsApp';
        return;
      }
      carrito = [];
      save();
      syncButtons();
      window.location.href = data.whatsapp;
    } catch (e) {
      alert('Error de conexión. Intenta de nuevo.');
      btnPedir.disabled = false;
      btnPedir.textContent = 'Confirmar por WhatsApp';
    }
  });

  // Tema
  const btnTheme = document.getElementById('toggleTheme');
  if (localStorage.getItem('tema') === 'dark') {
    document.body.classList.add('dark');
    if (btnTheme) btnTheme.textContent = '☀️';
  }
  btnTheme?.addEventListener('click', () => {
    document.body.classList.toggle('dark');
    const oscuro = document.body.classList.contains('dark');
    btnTheme.textContent = oscuro ? '☀️' : '🌙';
    localStorage.setItem('tema', oscuro ? 'dark' : 'light');
  });

  // Menú
  const menuToggle = document.getElementById('menuToggle');
  const headerMenu = document.querySelector('.header-center');
  menuToggle?.addEventListener('click', () => {
    const abierto = headerMenu.classList.toggle('open');
    menuToggle.textContent = abierto ? '✕' : '☰';
    menuToggle.setAttribute('aria-expanded', abierto);
  });

  renderCart();
  updateCount();
  syncButtons();
})();
</script>
</body>
</html>
