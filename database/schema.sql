-- VentaPOS — Esquema MVP Fase 1 (español)
-- Solo tablas mínimas para beta. Esquema completo: schema_rms_completo.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS empresas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    codigo VARCHAR(80) NOT NULL UNIQUE,
    tipo_negocio ENUM('bodega','ferreteria','farmacia','otro') NOT NULL DEFAULT 'bodega',
    ruc VARCHAR(11) NULL,
    telefono VARCHAR(20) NULL,
    direccion VARCHAR(255) NULL,
    configuracion JSON NULL,
    plan ENUM('beta','basico','negocio','pro') NOT NULL DEFAULT 'beta',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    prueba_hasta DATE NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NULL,
    nombre VARCHAR(120) NOT NULL,
    usuario VARCHAR(60) NOT NULL,
    email VARCHAR(150) NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('super_admin','dueno','cajero','supervisor') NOT NULL DEFAULT 'cajero',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    remember_token VARCHAR(100) NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_empresa_usuario (empresa_id, usuario),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categorias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    UNIQUE KEY uq_empresa_categoria (empresa_id, nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS productos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    categoria_id BIGINT UNSIGNED NULL,
    sku VARCHAR(50) NULL,
    codigo_barra VARCHAR(64) NULL,
    nombre VARCHAR(200) NOT NULL,
    unidad_medida VARCHAR(20) NOT NULL DEFAULT 'UND',
    costo_compra DECIMAL(12,4) NOT NULL DEFAULT 0,
    precio_venta DECIMAL(12,4) NOT NULL DEFAULT 0,
    afecto_igv TINYINT(1) NOT NULL DEFAULT 1,
    stock_disponible DECIMAL(12,4) NOT NULL DEFAULT 0,
    stock_minimo DECIMAL(12,4) NOT NULL DEFAULT 0,
    campo1 VARCHAR(100) NULL,
    campo2 VARCHAR(100) NULL,
    campo3 VARCHAR(100) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    INDEX idx_empresa_barra (empresa_id, codigo_barra)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS comprobantes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    numero_interno VARCHAR(30) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    valor_igv DECIMAL(12,2) NOT NULL DEFAULT 0,
    descuento DECIMAL(12,2) NOT NULL DEFAULT 0,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    medio_pago ENUM('efectivo','yape','plin','tarjeta','mixto') NOT NULL DEFAULT 'efectivo',
    estado ENUM('abierto','emitido','anulado') NOT NULL DEFAULT 'emitido',
    emitido_en DATETIME NOT NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    UNIQUE KEY uq_empresa_comprobante (empresa_id, numero_interno)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS detalle_comprobante (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comprobante_id BIGINT UNSIGNED NOT NULL,
    producto_id BIGINT UNSIGNED NOT NULL,
    descripcion_producto VARCHAR(200) NOT NULL,
    cantidad DECIMAL(12,4) NOT NULL,
    precio_unitario DECIMAL(12,4) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comprobante_id) REFERENCES comprobantes(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inventario_movimiento (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    producto_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NULL,
    tipo_movimiento ENUM('entrada','salida','venta','ajuste') NOT NULL,
    cantidad DECIMAL(12,4) NOT NULL,
    stock_anterior DECIMAL(12,4) NOT NULL,
    stock_nuevo DECIMAL(12,4) NOT NULL,
    referencia VARCHAR(100) NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS prospectos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_negocio VARCHAR(150) NOT NULL,
    nombre_contacto VARCHAR(120) NULL,
    telefono VARCHAR(20) NOT NULL,
    email VARCHAR(150) NULL,
    tipo_negocio VARCHAR(50) NULL,
    mensaje TEXT NULL,
    origen ENUM('landing','beta','whatsapp','referido') DEFAULT 'landing',
    estado ENUM('nuevo','contactado','demo','beta','cliente','descartado') DEFAULT 'nuevo',
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
