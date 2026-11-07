<?php
/**
 * Tienda pública de un vendedor — estilo profesional
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
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>No disponible</title><link rel="stylesheet" href="/styles.css"></head>
<body style="text-align:center;padding:60px 16px;font-family:system-ui">
<h2><?= e($tienda['nombre_comercial']) ?></h2>
<p><?= e($msg) ?></p>
<p><a href="/tiendas.html">Ver otras tiendas</a></p>
</body></html><?php
    exit;
}

$tiendaId = (int) $tienda['id'];
$porPagina = 12;
$pagina = max(1, (int) ($_GET['p'] ?? 1));
$q = trim((string) ($_GET['q'] ?? ''));
// Limitar longitud de búsqueda
if (mb_strlen($q) > 80) {
    $q = mb_substr($q, 0, 80);
}

$where = "tienda_id = ? AND estado = 'publicado'";
$params = [$tiendaId];
if ($q !== '') {
    $where .= " AND (nombre LIKE ? OR descripcion LIKE ? OR badge LIKE ?)";
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$totalStmt = db()->prepare("SELECT COUNT(*) FROM productos WHERE {$where}");
$totalStmt->execute($params);
$totalProductos = (int) $totalStmt->fetchColumn();
$totalPaginas = max(1, (int) ceil(max($totalProductos, 1) / $porPagina));
if ($totalProductos === 0) {
    $totalPaginas = 1;
}
if ($pagina > $totalPaginas) {
    $pagina = $totalPaginas;
}
$offset = ($pagina - 1) * $porPagina;

$ps = db()->prepare(
    "SELECT id, nombre, descripcion, precio, stock, badge, imagen_principal, es_preventa
     FROM productos
     WHERE {$where}
     ORDER BY orden, nombre
     LIMIT {$porPagina} OFFSET {$offset}"
);
$ps->execute($params);
$productos = $ps->fetchAll();

$colorP = $tienda['color_principal'] ?: '#1a1a2e';
$colorS = $tienda['color_secundario'] ?: '#e94560';
$wa = preg_replace('/\D+/', '', $tienda['whatsapp'] ?? '');
$nombreTienda = $tienda['nombre_comercial'];
$descCorta = $tienda['descripcion_corta'] ?: 'Coleccionables y piezas seleccionadas.';
$headerTag = trim((string)($tienda['header_tagline'] ?? '')) ?: 'Tienda independiente';
$footerTxt = trim((string)($tienda['footer_texto'] ?? '')) ?: 'Los pedidos se confirman por WhatsApp con esta tienda.';
$enviosTxt = trim((string)($tienda['envios_texto'] ?? ''));
$terminosTxt = trim((string)($tienda['terminos_texto'] ?? ''));
$facebook = trim((string)($tienda['facebook_url'] ?? ''));
$instagram = trim((string)($tienda['instagram_url'] ?? ''));
$extraLabel = trim((string)($tienda['menu_extra_label'] ?? ''));
$extraTexto = trim((string)($tienda['menu_extra_texto'] ?? ''));
$mostrarRed = !isset($tienda['mostrar_red']) || (int)$tienda['mostrar_red'] === 1;

// Normalizar rutas de medios (sin // al inicio, con / delante)
$logoRuta = trim((string)($tienda['logo_ruta'] ?? ''));
$logoRuta = ltrim($logoRuta, '/');
$bannerRuta = trim((string)($tienda['banner_ruta'] ?? ''));
$bannerRuta = ltrim($bannerRuta, '/');
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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --tienda-primary: <?= e($colorP) ?>;
      --tienda-secondary: <?= e($colorS) ?>;
    }
    .tienda-page main { max-width: 1100px; margin: 0 auto; padding: 0 16px 48px; }
    .tienda-page .cart-drawer { z-index: 120 !important; }
    .tienda-page #cartOverlay { z-index: 110 !important; }
    .tienda-page .cart-toast { z-index: 130 !important; }

    /* Header tienda */
    .store-header {
      position: sticky; top: 0; z-index: 50;
      backdrop-filter: blur(12px);
      background: color-mix(in srgb, var(--tienda-primary) 14%, rgba(12,12,14,.94));
      border-bottom: 1px solid color-mix(in srgb, var(--tienda-secondary) 28%, rgba(255,255,255,.1));
    }
    body:not(.dark) .store-header {
      background: color-mix(in srgb, var(--tienda-primary) 5%, #fff);
      border-bottom-color: color-mix(in srgb, var(--tienda-secondary) 20%, rgba(0,0,0,.08));
    }
    .store-header-inner {
      max-width: 1100px; margin: 0 auto; padding: 10px 16px;
      display: flex; align-items: center; justify-content: space-between; gap: 10px;
    }
    .store-brand { display: flex; align-items: center; gap: 10px; min-width: 0; color: inherit; text-decoration: none; }
    .store-logo { width: 40px; height: 40px; border-radius: 11px; object-fit: cover; flex-shrink: 0; }
    .store-logo-fallback {
      width: 40px; height: 40px; border-radius: 11px; display: grid; place-items: center;
      font-weight: 800; color: #fff; flex-shrink: 0;
      background: linear-gradient(135deg, var(--tienda-primary), var(--tienda-secondary));
    }
    .store-brand-text { display: flex; flex-direction: column; min-width: 0; }
    .store-brand-text strong { font-size: .98rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .store-brand-text span { font-size: .72rem; opacity: .7; }
    .store-header-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }

    .store-menu-btn {
      background: transparent; border: 1px solid rgba(255,255,255,.15);
      color: inherit; border-radius: 10px; width: 40px; height: 40px;
      font-size: 1.15rem; cursor: pointer;
    }
    body:not(.dark) .store-menu-btn { border-color: rgba(0,0,0,.12); }

    /* Panel menú */
    .store-nav {
      display: none; border-top: 1px solid rgba(255,255,255,.08);
      background: rgba(0,0,0,.2);
    }
    body:not(.dark) .store-nav { background: rgba(0,0,0,.03); border-top-color: rgba(0,0,0,.06); }
    .store-nav.open { display: block; }
    .store-nav-inner {
      max-width: 1100px; margin: 0 auto; padding: 10px 16px 14px;
      display: flex; flex-direction: column; gap: 4px;
    }
    .store-nav button, .store-nav a {
      text-align: left; background: transparent; border: none; color: inherit;
      font: inherit; font-size: .95rem; padding: 12px 10px; border-radius: 10px;
      cursor: pointer; text-decoration: none;
    }
    .store-nav button:hover, .store-nav a:hover { background: rgba(255,255,255,.06); }
    body:not(.dark) .store-nav button:hover, body:not(.dark) .store-nav a:hover { background: rgba(0,0,0,.05); }

    /* Hero */
    .tienda-hero-wrap { max-width: 820px; margin: 14px auto 8px; padding: 0 16px; }
    .tienda-hero {
      text-align: center; padding: 30px 20px 26px; border-radius: 20px;
      background:
        radial-gradient(120% 80% at 50% 0%, color-mix(in srgb, var(--tienda-secondary) 22%, transparent), transparent 55%),
        linear-gradient(180deg, rgba(255,255,255,.04), transparent);
      border: 1px solid color-mix(in srgb, var(--tienda-secondary) 32%, rgba(255,255,255,.08));
      box-shadow: 0 10px 36px rgba(0,0,0,.16);
    }
    body:not(.dark) .tienda-hero {
      background:
        radial-gradient(120% 80% at 50% 0%, color-mix(in srgb, var(--tienda-secondary) 14%, transparent), transparent 55%),
        linear-gradient(180deg, #fff, #f6f6f8);
      border-color: color-mix(in srgb, var(--tienda-secondary) 25%, rgba(0,0,0,.08));
      box-shadow: 0 10px 28px rgba(0,0,0,.07);
    }
    .tienda-hero-kicker {
      display: inline-block; margin-bottom: 10px; font-size: .7rem; font-weight: 700;
      letter-spacing: .08em; text-transform: uppercase; color: var(--tienda-secondary);
    }
    .tienda-hero h2 {
      margin: 0 0 8px; font-size: clamp(1.7rem, 5vw, 2.3rem);
      letter-spacing: -.03em; line-height: 1.15;
    }
    .tienda-hero p { margin: 0 auto; max-width: 42ch; opacity: .82; font-size: 1.02rem; line-height: 1.5; }
    .tienda-badge-red {
      display: inline-flex; margin-top: 14px; font-size: .72rem; font-weight: 700;
      letter-spacing: .04em; text-transform: uppercase; padding: 8px 14px; border-radius: 999px;
      color: #fff; border: none;
      background: linear-gradient(135deg, var(--tienda-primary), var(--tienda-secondary));
      box-shadow: 0 6px 16px color-mix(in srgb, var(--tienda-secondary) 32%, transparent);
    }

    .tienda-page .catalogo-title { text-align: center; margin: 32px auto 4px; padding: 0 16px; }
    .tienda-page .catalogo-title h2 {
      margin: 0 0 6px; font-size: 1.1rem; font-weight: 700;
      letter-spacing: .05em; text-transform: uppercase; opacity: .9;
    }
    .tienda-page .catalogo-title p { margin: 0; opacity: .65; font-size: .9rem; }
    .tienda-page .catalogo-title::after {
      content: ""; display: block; width: 48px; height: 3px; margin: 12px auto 0;
      border-radius: 999px;
      background: linear-gradient(90deg, var(--tienda-primary), var(--tienda-secondary));
    }

    .tienda-page #catalogo {
      display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 20px; padding: 16px 0 24px;
    }
    .tienda-page #catalogo .producto {
      max-width: 280px; width: 100%; margin: 0 auto; border-radius: 16px; overflow: hidden;
      transition: transform .18s ease, box-shadow .18s ease;
    }
    .tienda-page #catalogo .producto:hover {
      transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,.14);
    }
    .tienda-page #catalogo .producto .img-wrapper {
      aspect-ratio: 1; overflow: hidden; background: rgba(0,0,0,.04); cursor: zoom-in;
    }
    .tienda-page #catalogo .producto .producto-img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .tienda-page #catalogo .producto .info { padding: 12px 14px 14px; }
    .tienda-page #catalogo .producto .info h2 { font-size: .98rem; line-height: 1.3; margin: 0 0 4px; }
    .tienda-page #catalogo .producto .descripcion-oferta {
      font-size: .82rem; opacity: .7; margin: 0 0 6px;
      display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .tienda-page #catalogo .producto .precio { font-size: 1.05rem; font-weight: 700; margin: 4px 0 10px; }
    .tienda-page #catalogo .producto .boton { width: 100%; }
    .tienda-page .boton:not(.proximamente):not(.apartado):not(:disabled) {
      background: var(--tienda-primary); color: #fff !important;
    }
    .tienda-page .btn-whatsapp {
      background: #25d366 !important;
      color: #fff !important;
      border: none !important;
      font-weight: 700 !important;
      opacity: 1 !important;
    }
    .tienda-page .btn-whatsapp:disabled { opacity: .7 !important; }
    .sin-productos { text-align: center; padding: 56px 16px; opacity: .75; grid-column: 1 / -1; }

    .store-paginacion {
      display: flex; align-items: center; justify-content: center; gap: 14px;
      padding: 8px 0 28px; flex-wrap: wrap;
    }
    .store-page-btn {
      display: inline-block; padding: 10px 16px; border-radius: 10px;
      text-decoration: none; font-weight: 600; font-size: .9rem;
      background: var(--tienda-primary); color: #fff !important;
    }
    .store-page-btn.disabled {
      opacity: .35; pointer-events: none; background: rgba(128,128,128,.35); color: inherit !important;
    }
    .store-page-info { font-size: .9rem; opacity: .75; }

    /* Footer */
    .store-footer {
      margin-top: 20px;
      border-top: 1px solid rgba(255,255,255,.08);
      background: linear-gradient(180deg, color-mix(in srgb, var(--tienda-primary) 12%, transparent), transparent);
    }
    body:not(.dark) .store-footer {
      border-top-color: rgba(0,0,0,.08);
      background: linear-gradient(180deg, color-mix(in srgb, var(--tienda-primary) 5%, #f4f4f6), #f4f4f6);
    }
    .store-footer-inner {
      max-width: 1100px; margin: 0 auto; padding: 36px 16px 20px;
      display: grid; gap: 28px;
      grid-template-columns: 1fr;
    }
    @media (min-width: 720px) {
      .store-footer-inner { grid-template-columns: 1.3fr 1fr 1fr; }
    }
    .store-footer h4 {
      margin: 0 0 12px; font-size: .78rem; letter-spacing: .06em;
      text-transform: uppercase; opacity: .7;
    }
    .store-footer-brand strong { display: block; font-size: 1.1rem; margin-bottom: 8px; }
    .store-footer p { margin: 0 0 8px; font-size: .9rem; opacity: .78; line-height: 1.45; }
    .store-footer-links { display: flex; flex-direction: column; gap: 10px; }
    .store-footer-links button, .store-footer-links a {
      background: none; border: none; padding: 0; text-align: left;
      color: var(--tienda-secondary); font: inherit; font-size: .92rem; font-weight: 600;
      cursor: pointer; text-decoration: underline; text-underline-offset: 3px;
      text-decoration-color: color-mix(in srgb, var(--tienda-secondary) 45%, transparent);
    }
    .store-footer-links button:hover, .store-footer-links a:hover {
      text-decoration-color: var(--tienda-secondary);
      filter: brightness(1.1);
    }

    .store-search-wrap {
      max-width: 520px; margin: 18px auto 6px; padding: 0 16px;
    }
    .store-search-box {
      display: flex; align-items: center; gap: 8px;
      border-radius: 12px; padding: 4px 4px 4px 14px;
      border: 1px solid rgba(255,255,255,.12);
      background: rgba(255,255,255,.04);
    }
    body:not(.dark) .store-search-box {
      border-color: rgba(0,0,0,.1);
      background: #fff;
      box-shadow: 0 2px 10px rgba(0,0,0,.05);
    }
    .store-search-box input {
      flex: 1; border: none; background: transparent; color: inherit;
      font: inherit; font-size: .95rem; padding: 10px 0; outline: none; min-width: 0;
    }
    .store-search-box button {
      border: none; border-radius: 10px; padding: 10px 14px;
      font-weight: 600; cursor: pointer; background: var(--tienda-primary); color: #fff;
    }
    .store-search-clear {
      font-size: .85rem; text-align: center; margin: 8px 0 0;
    }
    .store-search-clear a { color: var(--tienda-secondary); font-weight: 600; }
    .store-social { display: flex; flex-wrap: wrap; gap: 10px; }
    .store-social a {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 8px 12px; border-radius: 999px; font-size: .85rem; font-weight: 600;
      text-decoration: none; color: #fff;
      background: color-mix(in srgb, var(--tienda-primary) 80%, #000);
    }
    .store-social a.wa { background: #25d366; }
    .store-social a.fb { background: #1877f2; }
    .store-social a.ig { background: linear-gradient(45deg, #f58529, #dd2a7b, #8134af); }
    .store-footer-bottom {
      max-width: 1100px; margin: 0 auto; padding: 14px 16px 28px;
      border-top: 1px solid rgba(255,255,255,.06);
      font-size: .8rem; opacity: .55; text-align: center;
    }
    body:not(.dark) .store-footer-bottom { border-top-color: rgba(0,0,0,.06); }
    .store-footer-bottom a { color: var(--tienda-secondary); text-decoration: none; font-weight: 600; }

    /* Modal info (envíos, términos) */
    .store-modal-overlay {
      position: fixed; inset: 0; background: rgba(0,0,0,.55);
      display: none; align-items: center; justify-content: center;
      z-index: 80; padding: 16px;
    }
    .store-modal-overlay.show { display: flex; }
    .store-modal {
      background: #1a1d27; color: #eee; max-width: 520px; width: 100%;
      border-radius: 16px; padding: 22px 20px; max-height: 80vh; overflow: auto;
      box-shadow: 0 20px 50px rgba(0,0,0,.4);
    }
    body:not(.dark) .store-modal { background: #fff; color: #1a1a1a; }
    .store-modal h3 { margin: 0 0 12px; font-size: 1.15rem; }
    .store-modal .store-modal-body { white-space: pre-wrap; font-size: .95rem; line-height: 1.5; opacity: .9; }
    .store-modal-close {
      margin-top: 18px; width: 100%; border: none; border-radius: 10px;
      padding: 11px; font-weight: 600; cursor: pointer;
      background: var(--tienda-primary); color: #fff;
    }

    /* Confirm vaciar */
    .confirm-overlay {
      position: fixed; inset: 0; background: rgba(0,0,0,.65);
      display: none; align-items: center; justify-content: center; z-index: 200; padding: 16px;
    }
    .confirm-overlay.show { display: flex; }
    .confirm-modal {
      background: #1a1d27; color: #eee; border-radius: 16px; padding: 22px;
      max-width: 360px; width: 100%; text-align: center;
    }
    body:not(.dark) .confirm-modal { background: #fff; color: #1a1a1a; }
    .confirm-modal h3 { margin: 0 0 8px; }
    .confirm-modal p { margin: 0 0 16px; opacity: .8; font-size: .92rem; }
    .confirm-actions { display: flex; gap: 10px; justify-content: center; }
    .confirm-actions button {
      border: none; border-radius: 10px; padding: 10px 16px; font-weight: 600; cursor: pointer;
    }
    .btn-cancelar { background: rgba(255,255,255,.1); color: inherit; }
    body:not(.dark) .btn-cancelar { background: #eee; }
    .btn-confirmar { background: #e94560; color: #fff; }

    /* Image modal */
    .image-modal {
      position: fixed; inset: 0; background: rgba(0,0,0,.85);
      display: none; align-items: center; justify-content: center; z-index: 100; padding: 16px;
    }
    .image-modal.show { display: flex; }
    .image-modal img { max-width: min(960px, 100%); max-height: 90vh; border-radius: 12px; object-fit: contain; }
    .image-modal .close-modal {
      position: absolute; top: 16px; right: 20px; color: #fff; font-size: 1.6rem;
      cursor: pointer; background: none; border: none;
    }
  </style>
</head>
<body class="tienda-page">

<header class="store-header">
  <div class="store-header-inner">
    <a class="store-brand" href="?slug=<?= e($slug) ?>">
      <?php if ($logoRuta !== ''): ?>
        <img src="/<?= e($logoRuta) ?>" alt="<?= e($nombreTienda) ?>" class="store-logo"
             onerror="this.onerror=null;this.style.display='none';this.nextElementSibling&&(this.nextElementSibling.style.display='grid');">
        <span class="store-logo-fallback" style="display:none"><?= e(mb_strtoupper(mb_substr($nombreTienda, 0, 1))) ?></span>
      <?php else: ?>
        <span class="store-logo-fallback"><?= e(mb_strtoupper(mb_substr($nombreTienda, 0, 1))) ?></span>
      <?php endif; ?>
      <div class="store-brand-text">
        <strong><?= e($nombreTienda) ?></strong>
        <span><?= e($headerTag ?: 'Tienda independiente') ?></span>
      </div>
    </a>
    <div class="store-header-actions">
      <button id="verCarrito" class="cart-btn" type="button" aria-label="Ver carrito">🛒 <span id="cartCount">0</span></button>
      <button id="toggleTheme" class="theme-btn" type="button" aria-label="Tema">🌙</button>
    </div>
  </div>
</header>

<div class="tienda-hero-wrap">
  <section class="tienda-hero">
    <div class="tienda-hero-kicker">Tienda oficial de la red</div>
    <h2><?= e($nombreTienda) ?></h2>
    <p><?= e($descCorta) ?></p>
    <div class="tienda-badge-red">Mi Tesoro MX | Tu Tienda</div>
  </section>
</div>

<div class="store-search-wrap">
  <form class="store-search-box" method="get" action="/tiendas/<?= e(rawurlencode($slug)) ?>" role="search">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar producto..." autocomplete="off" aria-label="Buscar productos">
    <button type="submit">Buscar</button>
  </form>
  <?php if ($q !== ''): ?>
    <p class="store-search-clear">
      Resultados para “<?= e($q) ?>” ·
      <a href="/tiendas/<?= e(rawurlencode($slug)) ?>">Ver todo</a>
    </p>
  <?php endif; ?>
</div>

<div class="catalogo-title">
  <h2>Catálogo</h2>
  <p><?= (int)$totalProductos ?> producto<?= $totalProductos === 1 ? '' : 's' ?><?= $q !== '' ? ' encontrado' . ($totalProductos === 1 ? '' : 's') : ' disponible' . ($totalProductos === 1 ? '' : 's') ?><?= $totalPaginas > 1 ? ' · Página ' . (int)$pagina . ' de ' . (int)$totalPaginas : '' ?></p>
</div>

<main>
  <section id="catalogo" aria-label="Catálogo de la tienda">
    <?php if (!$productos): ?>
      <p class="sin-productos"><?= $q !== '' ? 'No hay productos que coincidan con tu búsqueda.' : 'Esta tienda aún no tiene productos publicados.' ?></p>
    <?php else: foreach ($productos as $p):
      $badge = trim((string)($p['badge'] ?? ''));
      $badgeClass = $badge ? strtolower(preg_replace('/\s+/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $badge) ?: $badge)) : '';
      $img = $p['imagen_principal'] ?: 'imgs/placeholder.png';
      $stock = (int)$p['stock'];
      $precio = (float)$p['precio'];
    ?>
      <article class="producto"
               data-id="<?= (int)$p['id'] ?>"
               data-nombre="<?= e($p['nombre']) ?>"
               data-precio="<?= e((string)$precio) ?>"
               data-stock="<?= $stock ?>"
               data-imagen="<?= e($img) ?>">
        <?php if ($badge): ?><span class="badge <?= e($badgeClass) ?>"><?= e($badge) ?></span><?php endif; ?>
        <div class="img-wrapper">
          <img src="/<?= e($img) ?>" alt="<?= e($p['nombre']) ?>" class="producto-img" loading="lazy"
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
    <?php endforeach; endif; ?>
  </section>

  <?php if ($totalPaginas > 1): ?>
  <nav class="store-paginacion" aria-label="Paginación">
    <?php
      $basePage = '/tiendas/' . rawurlencode($slug);
      $qParam = $q !== '' ? '&q=' . rawurlencode($q) : '';
    ?>
    <?php if ($pagina > 1): ?>
      <a class="store-page-btn" href="<?= e($basePage) ?>?p=<?= $pagina - 1 ?><?= e($qParam) ?>">← Anterior</a>
    <?php else: ?>
      <span class="store-page-btn disabled">← Anterior</span>
    <?php endif; ?>

    <span class="store-page-info"><?= (int)$pagina ?> / <?= (int)$totalPaginas ?></span>

    <?php if ($pagina < $totalPaginas): ?>
      <a class="store-page-btn" href="<?= e($basePage) ?>?p=<?= $pagina + 1 ?><?= e($qParam) ?>">Siguiente →</a>
    <?php else: ?>
      <span class="store-page-btn disabled">Siguiente →</span>
    <?php endif; ?>
  </nav>
  <?php endif; ?>
</main>

<footer class="store-footer">
  <div class="store-footer-inner">
    <div class="store-footer-brand">
      <h4>Tienda</h4>
      <strong><?= e($nombreTienda) ?></strong>
      <p><?= e($descCorta) ?></p>
      <p><?= e($footerTxt) ?></p>
    </div>
    <div>
      <h4>Información</h4>
      <div class="store-footer-links">
        <?php if ($enviosTxt !== ''): ?>
          <button type="button" data-modal="envios">Envíos</button>
        <?php endif; ?>
        <?php if ($terminosTxt !== ''): ?>
          <button type="button" data-modal="terminos">Términos de esta tienda</button>
        <?php endif; ?>
        <?php if ($extraLabel !== '' && $extraTexto !== ''): ?>
          <button type="button" data-modal="extra"><?= e($extraLabel) ?></button>
        <?php endif; ?>
        <a href="/terminos-tu-tienda.html">Términos de Mi Tesoro MX | Tu Tienda</a>
        <a href="/reportar-tienda.html?slug=<?= e(rawurlencode($slug)) ?>&amp;nombre=<?= e(rawurlencode($nombreTienda)) ?>">Reportar esta tienda</a>
      </div>
    </div>
    <div>
      <h4>Contacto</h4>
      <div class="store-social">
        <?php if ($wa !== ''): ?>
          <a class="wa" href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener">WhatsApp</a>
        <?php endif; ?>
        <?php if ($facebook !== ''): ?>
          <a class="fb" href="<?= e($facebook) ?>" target="_blank" rel="noopener">Facebook</a>
        <?php endif; ?>
        <?php if ($instagram !== ''): ?>
          <a class="ig" href="<?= e($instagram) ?>" target="_blank" rel="noopener">Instagram</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="store-footer-bottom">
    <?php if ($mostrarRed): ?>
      Alojado en <a href="/tiendas.html">Mi Tesoro MX | Tu Tienda</a>
    <?php else: ?>
      <?= e($nombreTienda) ?>
    <?php endif; ?>
  </div>
</footer>

<aside id="cartDrawer" class="cart-drawer" aria-label="Carrito">
  <div class="cart-header">
    <h2>🛒 Carrito · <?= e($nombreTienda) ?></h2>
    <button id="cerrarDrawer" type="button" aria-label="Cerrar">✕</button>
  </div>
  <div id="cartItems" class="cart-items"></div>
  <div class="cart-footer">
    <p id="cartTotal">Tu carrito está vacío</p>
    <button type="button" id="btnSeguirComprando" class="btn-seguir-comprando" style="display:none">Seguir comprando</button>
    <button type="button" id="btnPedirWhats" class="btn-whatsapp" style="display:none">Confirmar por WhatsApp</button>
    <button id="vaciarCarrito" class="btn-vaciar" type="button" disabled>Vaciar carrito</button>
  </div>
</aside>
<div id="cartOverlay"></div>

<div id="cartToast" class="cart-toast" role="status" style="display:none">
  <strong id="toastTitle">Agregado al carrito</strong>
  <p id="toastText"></p>
  <div class="toast-actions">
    <button id="toastVerCarrito" type="button">Ver carrito</button>
    <button id="toastCerrar" type="button" aria-label="Cerrar">✕</button>
  </div>
</div>

<div class="store-modal-overlay" id="infoModal" role="dialog" aria-modal="true">
  <div class="store-modal">
    <h3 id="infoModalTitle"></h3>
    <div class="store-modal-body" id="infoModalBody"></div>
    <button type="button" class="store-modal-close" id="infoModalClose">Cerrar</button>
  </div>
</div>

<div class="confirm-overlay" id="confirmVaciar" role="dialog" aria-modal="true">
  <div class="confirm-modal">
    <h3>¿Vaciar carrito?</h3>
    <p>Se eliminarán todos los productos de esta tienda.</p>
    <div class="confirm-actions">
      <button type="button" class="btn-cancelar" id="cancelVaciar">Cancelar</button>
      <button type="button" class="btn-confirmar" id="confirmVaciarBtn">Sí, vaciar</button>
    </div>
  </div>
</div>

<div class="image-modal" id="imageModal" role="dialog" aria-modal="true">
  <button type="button" class="close-modal" id="closeImageModal" aria-label="Cerrar">✕</button>
  <img id="modalImage" src="" alt="Imagen ampliada">
</div>

<script>
(function () {
  const SLUG = <?= json_encode($slug, JSON_UNESCAPED_UNICODE) ?>;
  const KEY = 'carrito_tienda_' + SLUG;
  const MODALS = {
    envios: { title: 'Envíos', body: <?= json_encode($enviosTxt, JSON_UNESCAPED_UNICODE) ?> },
    terminos: { title: 'Términos y condiciones', body: <?= json_encode($terminosTxt, JSON_UNESCAPED_UNICODE) ?> },
    extra: { title: <?= json_encode($extraLabel ?: 'Info', JSON_UNESCAPED_UNICODE) ?>, body: <?= json_encode($extraTexto, JSON_UNESCAPED_UNICODE) ?> }
  };

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
  const infoModal = document.getElementById('infoModal');
  const confirmBox = document.getElementById('confirmVaciar');
  const imageModal = document.getElementById('imageModal');
  const modalImage = document.getElementById('modalImage');
  const storeNav = document.getElementById('storeNav');

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
  function escapeHtml(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function renderCart() {
    if (!cartItems || !cartTotal) return;
    if (!carrito.length) {
      cartItems.innerHTML = '';
      cartTotal.innerHTML = '<div class="cart-empty"><p class="cart-empty-title">Tu carrito está vacío</p><p class="cart-empty-sub">Agrega productos de esta tienda.</p></div>';
      if (btnPedir) btnPedir.style.display = 'none';
      if (btnSeguir) btnSeguir.style.display = 'none';
      if (btnVaciar) btnVaciar.disabled = true;
      return;
    }
    if (btnPedir) {
      btnPedir.style.display = 'block';
      btnPedir.disabled = false;
      btnPedir.textContent = 'Confirmar por WhatsApp';
    }
    if (btnSeguir) btnSeguir.style.display = 'block';
    if (btnVaciar) btnVaciar.disabled = false;
    let subtotal = 0;
    cartItems.innerHTML = carrito.map((item, index) => {
      subtotal += item.precio * item.cantidad;
      return `<div class="cart-item">
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
    cartTotal.innerHTML = `<div class="cart-summary">
      <div class="cart-summary-row cart-summary-total"><span>Total</span><span>${money(subtotal)}</span></div>
      <p class="cart-summary-note">El envío lo confirma el vendedor por WhatsApp.</p>
    </div>`;
    cartItems.querySelectorAll('.cart-qty-btn').forEach(btn => {
      btn.onclick = () => {
        const i = Number(btn.dataset.index);
        if (!carrito[i]) return;
        if (btn.dataset.action === 'plus') {
          if (carrito[i].cantidad < (carrito[i].stock || 99)) carrito[i].cantidad++;
        } else {
          if (carrito[i].cantidad <= 1) carrito.splice(i, 1);
          else carrito[i].cantidad--;
        }
        save(); syncButtons();
      };
    });
    cartItems.querySelectorAll('.cart-remove').forEach(btn => {
      btn.onclick = () => { carrito.splice(Number(btn.dataset.index), 1); save(); syncButtons(); };
    });
  }

  function showToast(nombre) {
    if (!toast) return;
    document.getElementById('toastTitle').textContent = 'Agregado al carrito';
    document.getElementById('toastText').textContent = '"' + nombre + '" se agregó al carrito';
    toast.style.display = 'block';
    toast.classList.add('show');
    clearTimeout(toast._t);
    toast._t = setTimeout(() => { toast.classList.remove('show'); toast.style.display = 'none'; }, 3200);
  }
  function openDrawer() { drawer?.classList.add('open'); overlay?.classList.add('show'); }
  function closeDrawer() { drawer?.classList.remove('open'); overlay?.classList.remove('show'); }

  function syncButtons() {
    document.querySelectorAll('.producto').forEach(card => {
      const id = Number(card.dataset.id);
      const btn = card.querySelector('.boton');
      if (!btn) return;
      const en = carrito.find(c => c.id === id);
      if (en) { btn.textContent = 'En carrito'; btn.classList.add('apartado'); btn.disabled = true; }
      else { btn.textContent = 'Agregar al carrito'; btn.classList.remove('apartado'); btn.disabled = false; }
    });
  }

  document.querySelectorAll('.producto .boton').forEach(btn => {
    btn.addEventListener('click', () => {
      const card = btn.closest('.producto');
      const id = Number(card.dataset.id);
      const stock = Number(card.dataset.stock);
      const nombre = card.dataset.nombre;
      const precio = Number(card.dataset.precio);
      const imagen = card.dataset.imagen || '';
      const found = carrito.find(x => x.id === id);
      if (found) { if (found.cantidad >= stock) return; found.cantidad++; }
      else carrito.push({ id, nombre, precio, cantidad: 1, stock, imagen });
      save(); syncButtons(); showToast(nombre);
    });
  });

  document.getElementById('verCarrito')?.addEventListener('click', openDrawer);
  document.getElementById('cerrarDrawer')?.addEventListener('click', closeDrawer);
  overlay?.addEventListener('click', closeDrawer);
  btnSeguir?.addEventListener('click', closeDrawer);
  document.getElementById('toastVerCarrito')?.addEventListener('click', () => { toast.style.display = 'none'; openDrawer(); });
  document.getElementById('toastCerrar')?.addEventListener('click', () => { toast.style.display = 'none'; });

  btnVaciar?.addEventListener('click', () => {
    if (!carrito.length) return;
    confirmBox.classList.add('show');
  });
  document.getElementById('cancelVaciar')?.addEventListener('click', () => confirmBox.classList.remove('show'));
  document.getElementById('confirmVaciarBtn')?.addEventListener('click', () => {
    carrito = []; save(); syncButtons();
    confirmBox.classList.remove('show');
    closeDrawer();
  });

  btnPedir?.addEventListener('click', async () => {
    if (!carrito.length) return;
    btnPedir.disabled = true;
    btnPedir.textContent = 'Procesando…';
    try {
      const res = await fetch('/api/pedido.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ slug: SLUG, items: carrito.map(i => ({ id: i.id, cantidad: i.cantidad })), tipo_pago: 'Pago total' })
      });
      const data = await res.json();
      if (!data.ok) {
        alert(data.error || 'No se pudo crear el pedido');
        btnPedir.disabled = false;
        btnPedir.textContent = 'Confirmar por WhatsApp';
        return;
      }
      carrito = []; save(); syncButtons();
      window.location.href = data.whatsapp;
    } catch (e) {
      alert('Error de conexión. Intenta de nuevo.');
      btnPedir.disabled = false;
      btnPedir.textContent = 'Confirmar por WhatsApp';
    }
  });

  // Zoom imagen
  document.querySelectorAll('.producto .img-wrapper').forEach(w => {
    w.addEventListener('click', () => {
      const src = w.querySelector('img')?.src;
      if (!src) return;
      modalImage.src = src;
      imageModal.classList.add('show');
    });
  });
  document.getElementById('closeImageModal')?.addEventListener('click', () => imageModal.classList.remove('show'));
  imageModal?.addEventListener('click', (e) => { if (e.target === imageModal) imageModal.classList.remove('show'); });

  // Menú
  document.getElementById('storeMenuBtn')?.addEventListener('click', () => {
    storeNav?.classList.toggle('open');
  });
  function openInfo(key) {
    const m = MODALS[key];
    if (!m || !m.body) return;
    document.getElementById('infoModalTitle').textContent = m.title;
    document.getElementById('infoModalBody').textContent = m.body;
    infoModal.classList.add('show');
    storeNav?.classList.remove('open');
  }
  document.querySelectorAll('[data-modal]').forEach(el => {
    el.addEventListener('click', () => openInfo(el.getAttribute('data-modal')));
  });
  document.getElementById('infoModalClose')?.addEventListener('click', () => infoModal.classList.remove('show'));
  infoModal?.addEventListener('click', (e) => { if (e.target === infoModal) infoModal.classList.remove('show'); });
  document.querySelectorAll('[data-scroll]').forEach(el => {
    el.addEventListener('click', () => {
      document.getElementById(el.getAttribute('data-scroll'))?.scrollIntoView({ behavior: 'smooth' });
      storeNav?.classList.remove('open');
    });
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

  // Si el usuario vuelve del WhatsApp, reiniciar estado del botón
  window.addEventListener('pageshow', () => {
    if (btnPedir) {
      btnPedir.disabled = false;
      btnPedir.textContent = 'Confirmar por WhatsApp';
    }
  });

  renderCart(); updateCount(); syncButtons();
})();
</script>
</body>
</html>
