-- ============================================================
-- MI TESORO MX | TU TIENDA
-- Schema inicial - MariaDB 11.8
-- NO destructivo: solo CREATE TABLE IF NOT EXISTS
-- ============================================================

USE dbs16302678;

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ------------------------------------------------------------
-- 1. Administradores
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_usuarios (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email         VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  nombre        VARCHAR(120) NOT NULL,
  activo        TINYINT(1) NOT NULL DEFAULT 1,
  ultimo_login  DATETIME NULL,
  creado_en     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. Vendedores
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS vendedores (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre        VARCHAR(150) NOT NULL,
  email         VARCHAR(190) NULL,
  telefono      VARCHAR(30) NULL,
  whatsapp      VARCHAR(30) NULL,
  notas         TEXT NULL,
  estado        ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  creado_en     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_vendedores_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. Tiendas
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tiendas (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  vendedor_id       INT UNSIGNED NULL,  -- NULL = tienda principal (Mi Tesoro MX)
  nombre_comercial  VARCHAR(150) NOT NULL,
  slug              VARCHAR(80) NOT NULL,
  descripcion_corta VARCHAR(300) NULL,
  logo_ruta         VARCHAR(255) NULL,
  banner_ruta       VARCHAR(255) NULL,
  whatsapp          VARCHAR(30) NOT NULL,
  color_principal   VARCHAR(7) NOT NULL DEFAULT '#1a1a2e',
  color_secundario  VARCHAR(7) NOT NULL DEFAULT '#e94560',
  color_fondo       VARCHAR(7) NOT NULL DEFAULT '#ffffff',
  tipografia        VARCHAR(80) NOT NULL DEFAULT 'system-ui',
  estilo_tarjetas   VARCHAR(40) NOT NULL DEFAULT 'default',
  max_productos     SMALLINT UNSIGNED NOT NULL DEFAULT 50,
  es_principal      TINYINT(1) NOT NULL DEFAULT 0,
  estado            ENUM('activa','suspendida','borrador') NOT NULL DEFAULT 'borrador',
  fecha_activacion  DATE NULL,
  fecha_vencimiento DATE NULL,
  creado_en         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_tiendas_slug (slug),
  INDEX idx_tiendas_estado (estado),
  INDEX idx_tiendas_vencimiento (fecha_vencimiento),
  CONSTRAINT fk_tiendas_vendedor
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. Categorías (por tienda)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tienda_id  INT UNSIGNED NOT NULL,
  nombre     VARCHAR(100) NOT NULL,
  slug       VARCHAR(100) NOT NULL,
  orden      SMALLINT NOT NULL DEFAULT 0,
  activa     TINYINT(1) NOT NULL DEFAULT 1,
  creado_en  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cat_tienda_slug (tienda_id, slug),
  INDEX idx_cat_tienda (tienda_id),
  CONSTRAINT fk_cat_tienda
    FOREIGN KEY (tienda_id) REFERENCES tiendas(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. Productos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS productos (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tienda_id       INT UNSIGNED NOT NULL,
  categoria_id    INT UNSIGNED NULL,
  nombre          VARCHAR(200) NOT NULL,
  descripcion     TEXT NULL,
  precio          DECIMAL(10,2) NOT NULL DEFAULT 0,
  stock           INT NOT NULL DEFAULT 0,
  stock_inicial   INT NOT NULL DEFAULT 0,
  badge           VARCHAR(60) NULL,
  imagen_principal VARCHAR(255) NULL,
  estado          ENUM('publicado','oculto','agotado','borrador') NOT NULL DEFAULT 'borrador',
  es_preventa     TINYINT(1) NOT NULL DEFAULT 0,
  orden           INT NOT NULL DEFAULT 0,
  publicado_en    DATETIME NULL,
  creado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_prod_tienda (tienda_id),
  INDEX idx_prod_tienda_estado (tienda_id, estado),
  INDEX idx_prod_badge (badge),
  INDEX idx_prod_nombre (nombre),
  CONSTRAINT fk_prod_tienda
    FOREIGN KEY (tienda_id) REFERENCES tiendas(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_prod_categoria
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. Imágenes adicionales de producto
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS producto_imagenes (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  producto_id INT UNSIGNED NOT NULL,
  ruta        VARCHAR(255) NOT NULL,
  es_principal TINYINT(1) NOT NULL DEFAULT 0,
  orden       SMALLINT NOT NULL DEFAULT 0,
  creado_en   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_img_producto (producto_id),
  CONSTRAINT fk_img_producto
    FOREIGN KEY (producto_id) REFERENCES productos(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 7. Suscripciones
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS suscripciones (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tienda_id         INT UNSIGNED NOT NULL,
  precio_mensual    DECIMAL(10,2) NOT NULL DEFAULT 300.00,
  fecha_inicio      DATE NOT NULL,
  fecha_vencimiento DATE NOT NULL,
  estado            ENUM('activa','vencida','suspendida','cancelada') NOT NULL DEFAULT 'activa',
  creado_en         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_sus_tienda (tienda_id),
  INDEX idx_sus_vencimiento (fecha_vencimiento),
  INDEX idx_sus_estado (estado),
  CONSTRAINT fk_sus_tienda
    FOREIGN KEY (tienda_id) REFERENCES tiendas(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 8. Pagos (registro manual)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pagos (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tienda_id       INT UNSIGNED NOT NULL,
  suscripcion_id  INT UNSIGNED NULL,
  monto           DECIMAL(10,2) NOT NULL,
  moneda          CHAR(3) NOT NULL DEFAULT 'MXN',
  metodo          VARCHAR(60) NULL,          -- transferencia, oxxo, etc.
  referencia      VARCHAR(120) NULL,
  periodo_inicio  DATE NULL,
  periodo_fin     DATE NULL,
  notas           TEXT NULL,
  registrado_por  INT UNSIGNED NULL,         -- admin_usuarios.id
  creado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pagos_tienda (tienda_id),
  INDEX idx_pagos_fecha (creado_en),
  CONSTRAINT fk_pagos_tienda
    FOREIGN KEY (tienda_id) REFERENCES tiendas(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_pagos_suscripcion
    FOREIGN KEY (suscripcion_id) REFERENCES suscripciones(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_pagos_admin
    FOREIGN KEY (registrado_por) REFERENCES admin_usuarios(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 9. Pedidos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pedidos (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tienda_id       INT UNSIGNED NOT NULL,
  codigo          VARCHAR(20) NOT NULL,      -- ej. MT-20261102-001
  nombre_cliente  VARCHAR(150) NULL,
  telefono        VARCHAR(30) NULL,
  codigo_postal   VARCHAR(10) NULL,
  tipo_pago       VARCHAR(40) NULL,          -- Pago total / Apartado 30%
  subtotal        DECIMAL(10,2) NOT NULL DEFAULT 0,
  descuento       DECIMAL(10,2) NOT NULL DEFAULT 0,
  costo_envio     DECIMAL(10,2) NOT NULL DEFAULT 0,
  total           DECIMAL(10,2) NOT NULL DEFAULT 0,
  estado          ENUM('pendiente','confirmado','cancelado','enviado') NOT NULL DEFAULT 'pendiente',
  notas           TEXT NULL,
  creado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_pedidos_codigo (codigo),
  INDEX idx_pedidos_tienda (tienda_id),
  INDEX idx_pedidos_estado (estado),
  CONSTRAINT fk_pedidos_tienda
    FOREIGN KEY (tienda_id) REFERENCES tiendas(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 10. Ítems del pedido
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pedido_items (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pedido_id       INT UNSIGNED NOT NULL,
  producto_id     INT UNSIGNED NULL,         -- puede quedar NULL si el producto se borra después
  nombre_producto VARCHAR(200) NOT NULL,     -- snapshot
  precio_unitario DECIMAL(10,2) NOT NULL,
  cantidad        SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  subtotal        DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_items_pedido
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_items_producto
    FOREIGN KEY (producto_id) REFERENCES productos(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 11. Logs de administración
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_logs (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id    INT UNSIGNED NULL,
  accion      VARCHAR(80) NOT NULL,
  entidad     VARCHAR(40) NULL,             -- tienda, producto, pago...
  entidad_id  INT UNSIGNED NULL,
  detalle     TEXT NULL,
  ip          VARCHAR(45) NULL,
  creado_en   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_logs_fecha (creado_en),
  INDEX idx_logs_entidad (entidad, entidad_id),
  CONSTRAINT fk_logs_admin
    FOREIGN KEY (admin_id) REFERENCES admin_usuarios(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Datos iniciales mínimos
-- ------------------------------------------------------------
-- Tienda principal (Mi Tesoro MX)
INSERT INTO tiendas (
  vendedor_id, nombre_comercial, slug, descripcion_corta,
  whatsapp, es_principal, estado, max_productos,
  fecha_activacion
) VALUES (
  NULL,
  'Mi Tesoro MX',
  'principal',
  'Coleccionables de cine, geek y cultura pop',
  '524761002824',
  1,
  'activa',
  9999,
  CURDATE()
) ON DUPLICATE KEY UPDATE nombre_comercial = VALUES(nombre_comercial);