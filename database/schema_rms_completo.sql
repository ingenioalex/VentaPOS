-- VentaPOS — Esquema completo en ESPAÑOL
-- MySQL 8.0+ · Basado en rms_db.sql + multi-empresa SaaS
-- Convención: docs/NOMENCLATURA_BD.md

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─── EMPRESAS (multi-empresa SaaS) ────────────────────────────────
CREATE TABLE IF NOT EXISTS empresas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    codigo VARCHAR(80) NOT NULL UNIQUE COMMENT 'subdominio: bodega-juan',
    tipo_negocio ENUM('bodega','ferreteria','farmacia','retail','otro') DEFAULT 'bodega',
    ruc VARCHAR(11) NULL,
    telefono VARCHAR(20) NULL,
    direccion TEXT NULL,
    logo_ruta VARCHAR(255) NULL,
    moneda CHAR(3) NOT NULL DEFAULT 'PEN',
    tasa_igv DECIMAL(5,2) NOT NULL DEFAULT 18.00,
    configuracion JSON NULL COMMENT 'terminal, local, impresora, etc.',
    plan ENUM('beta','basico','negocio','pro') DEFAULT 'beta',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    prueba_hasta DATE NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── USUARIOS ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NULL,
    nombre VARCHAR(120) NOT NULL,
    usuario VARCHAR(60) NOT NULL COMMENT 'login',
    email VARCHAR(150) NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('super_admin','admin','dueno','cajero','supervisor','digitador','jefe') DEFAULT 'cajero',
    es_supervisor TINYINT(1) NOT NULL DEFAULT 0,
    conectado TINYINT(1) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    remember_token VARCHAR(100) NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_empresa_usuario (empresa_id, usuario),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── SESIONES (login por terminal) ────────────────────────────────
CREATE TABLE IF NOT EXISTS sesiones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    terminal VARCHAR(20) NOT NULL,
    local VARCHAR(100) NULL,
    tipo_conexion ENUM('login','logout','test') DEFAULT 'login',
    inicio_en DATETIME NOT NULL,
    fin_en DATETIME NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── CATEGORÍAS (departamento → subdepartamento → clase) ──────────
CREATE TABLE IF NOT EXISTS departamentos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_empresa_depto (empresa_id, nombre),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS subdepartamentos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    departamento_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    subdepartamento_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (subdepartamento_id) REFERENCES subdepartamentos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── CAMPOS PERSONALIZADOS POR EMPRESA ────────────────────────────
CREATE TABLE IF NOT EXISTS campos_producto (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    clave VARCHAR(50) NOT NULL,
    etiqueta VARCHAR(100) NOT NULL,
    tipo ENUM('texto','numero','fecha','lista') DEFAULT 'texto',
    opciones JSON NULL,
    obligatorio TINYINT(1) NOT NULL DEFAULT 0,
    orden INT NOT NULL DEFAULT 0,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_empresa_campo (empresa_id, clave),
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── PRODUCTOS ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS productos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    clase_id BIGINT UNSIGNED NULL,
    sku VARCHAR(50) NULL,
    codigo_barra VARCHAR(64) NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion_corta VARCHAR(100) NULL,
    descripcion_larga TEXT NULL,
    unidad_medida VARCHAR(20) DEFAULT 'UND',
    marca VARCHAR(100) NULL,
    registro_sanitario VARCHAR(50) NULL,
    costo_compra DECIMAL(12,4) DEFAULT 0,
    costo_promedio DECIMAL(12,4) DEFAULT 0,
    valor_venta DECIMAL(12,4) DEFAULT 0,
    precio_venta DECIMAL(12,4) DEFAULT 0,
    afecto_igv TINYINT(1) DEFAULT 1,
    valor_igv DECIMAL(12,4) DEFAULT 0,
    stock_contable DECIMAL(12,4) DEFAULT 0,
    stock_disponible DECIMAL(12,4) DEFAULT 0,
    stock_minimo DECIMAL(12,4) DEFAULT 0,
    pesable TINYINT(1) DEFAULT 0,
    campo1 VARCHAR(100) NULL,
    campo2 VARCHAR(100) NULL,
    campo3 VARCHAR(100) NULL,
    campos_extra JSON NULL,
    estado VARCHAR(20) DEFAULT 'activo',
    activo TINYINT(1) DEFAULT 1,
    actualizado_por VARCHAR(60) NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (clase_id) REFERENCES clases(id) ON DELETE SET NULL,
    INDEX idx_empresa_barra (empresa_id, codigo_barra),
    INDEX idx_empresa_sku (empresa_id, sku)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── HISTORIAL PRECIOS ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS producto_precio (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    producto_id BIGINT UNSIGNED NOT NULL,
    descripcion_larga TEXT NULL,
    costo_anterior DECIMAL(12,4) NULL,
    costo_nuevo DECIMAL(12,4) NULL,
    costo_promedio DECIMAL(12,4) NULL,
    valor_venta_anterior DECIMAL(12,4) NULL,
    precio_venta_anterior DECIMAL(12,4) NULL,
    valor_venta_nuevo DECIMAL(12,4) NULL,
    precio_venta_nuevo DECIMAL(12,4) NULL,
    actualizado_por VARCHAR(60) NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── CLIENTES ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS clientes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    documento_tipo VARCHAR(10) DEFAULT 'DNI',
    documento_numero VARCHAR(20) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    direccion TEXT NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    UNIQUE KEY uq_empresa_documento (empresa_id, documento_numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── TERMINALES (cajas) ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS terminales (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    tipo_comprobante VARCHAR(10) DEFAULT 'B',
    serie VARCHAR(10) NULL,
    numero VARCHAR(30) NULL,
    serie_impresora VARCHAR(50) NULL,
    local VARCHAR(100) NULL,
    activo TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── COMPROBANTES (cabecera venta) ────────────────────────────────
CREATE TABLE IF NOT EXISTS comprobantes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    cliente_id BIGINT UNSIGNED NULL,
    sesion_id BIGINT UNSIGNED NULL,
    terminal_id BIGINT UNSIGNED NULL,
    tipo VARCHAR(10) DEFAULT 'B' COMMENT 'B,F,NC,GR,NP,RE',
    serie VARCHAR(10) NULL,
    numero VARCHAR(30) NULL,
    numero_interno VARCHAR(30) NOT NULL,
    subtotal_afecto DECIMAL(12,2) DEFAULT 0,
    subtotal_inafecto DECIMAL(12,2) DEFAULT 0,
    subtotal DECIMAL(12,2) DEFAULT 0,
    valor_igv DECIMAL(12,2) DEFAULT 0,
    descuento DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) DEFAULT 0,
    medio_pago ENUM('efectivo','tarjeta','yape','plin','otros','mixto') DEFAULT 'efectivo',
    efectivo DECIMAL(12,2) DEFAULT 0,
    tarjeta DECIMAL(12,2) DEFAULT 0,
    otro_pago DECIMAL(12,2) DEFAULT 0,
    vuelto DECIMAL(12,2) DEFAULT 0,
    estado ENUM('abierto','emitido','suspendido','anulado','test') DEFAULT 'abierto',
    datos_suspendidos JSON NULL,
    observaciones TEXT NULL,
    dni_cliente VARCHAR(15) NULL,
    emitido_en DATETIME NOT NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    UNIQUE KEY uq_empresa_numero (empresa_id, numero_interno)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── DETALLE COMPROBANTE ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS detalle_comprobante (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comprobante_id BIGINT UNSIGNED NOT NULL,
    producto_id BIGINT UNSIGNED NOT NULL,
    descripcion_producto VARCHAR(200) NOT NULL,
    codigo_barra VARCHAR(64) NULL,
    unidad_medida VARCHAR(20) DEFAULT 'UND',
    cantidad DECIMAL(12,4) NOT NULL,
    precio_unitario DECIMAL(12,4) NOT NULL,
    igv DECIMAL(12,4) DEFAULT 0,
    subtotal DECIMAL(12,2) NOT NULL,
    descuento DECIMAL(12,2) DEFAULT 0,
    tipo_afectacion VARCHAR(20) NULL,
    estado VARCHAR(20) DEFAULT 'emitido',
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comprobante_id) REFERENCES comprobantes(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── VENTA (registro analítico por línea — como RMS original) ─────
CREATE TABLE IF NOT EXISTS venta (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    comprobante_id BIGINT UNSIGNED NOT NULL,
    producto_id BIGINT UNSIGNED NOT NULL,
    nombre_producto VARCHAR(100) NULL,
    cantidad DECIMAL(12,4) NOT NULL,
    precio_unitario DECIMAL(12,4) NOT NULL,
    igv DECIMAL(12,4) DEFAULT 0,
    subtotal DECIMAL(12,2) NOT NULL,
    descuento DECIMAL(12,2) DEFAULT 0,
    total DECIMAL(12,2) NOT NULL,
    fecha_venta DATETIME NOT NULL,
    cliente VARCHAR(100) NULL,
    dni VARCHAR(15) NULL,
    medio_pago VARCHAR(30) NULL,
    terminal_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NULL,
    anio INT NULL,
    mes INT NULL,
    mes_anio INT NULL,
    dia INT NULL,
    semana INT NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (comprobante_id) REFERENCES comprobantes(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── INVENTARIO MOVIMIENTO ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS inventario_movimiento (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    producto_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NULL,
    tipo_movimiento ENUM('entrada','salida','venta','ajuste','devolucion') NOT NULL,
    cantidad DECIMAL(12,4) NOT NULL,
    stock_anterior DECIMAL(12,4) NOT NULL,
    stock_nuevo DECIMAL(12,4) NOT NULL,
    motivo VARCHAR(200) NULL,
    referencia VARCHAR(100) NULL,
    comprobante_id BIGINT UNSIGNED NULL,
    observaciones TEXT NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── CIERRE CAJA ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS cierre_caja (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    terminal_id BIGINT UNSIGNED NULL,
    tipo_cierre ENUM('X','Z','rapido') DEFAULT 'X',
    inicio_en DATETIME NULL,
    fin_en DATETIME NOT NULL,
    monto_inicial DECIMAL(12,2) DEFAULT 0,
    monto_final DECIMAL(12,2) DEFAULT 0,
    total_ventas DECIMAL(12,2) DEFAULT 0,
    ticket_promedio DECIMAL(12,2) DEFAULT 0,
    total_efectivo DECIMAL(12,2) DEFAULT 0,
    total_tarjeta DECIMAL(12,2) DEFAULT 0,
    total_otros DECIMAL(12,2) DEFAULT 0,
    total_igv DECIMAL(12,2) DEFAULT 0,
    diferencia DECIMAL(12,2) DEFAULT 0,
    observaciones TEXT NULL,
    estado ENUM('abierto','cerrado') DEFAULT 'cerrado',
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── RETIRO EFECTIVO ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS retiro_efectivo (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED NOT NULL,
    terminal_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    monto DECIMAL(12,2) NOT NULL,
    motivo VARCHAR(100) NULL,
    observaciones TEXT NULL,
    autorizado_por VARCHAR(100) NULL,
    cierre_caja_id BIGINT UNSIGNED NULL,
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE,
    FOREIGN KEY (terminal_id) REFERENCES terminales(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── PROSPECTOS (landing / beta) ──────────────────────────────────
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
    creado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- Datos de prueba
INSERT INTO empresas (nombre, codigo, tipo_negocio, plan) VALUES
('Bodega El Sol', 'bodega-el-sol', 'bodega', 'beta'),
('Ferretería Max', 'ferreteria-max', 'ferreteria', 'beta');
