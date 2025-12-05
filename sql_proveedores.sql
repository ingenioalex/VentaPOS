-- =====================================================
-- SCRIPT SQL PARA MÓDULO DE GESTIÓN DE PROVEEDORES
-- =====================================================
-- Ejecutar este script manualmente si se requiere

-- Tabla de Proveedores
CREATE TABLE IF NOT EXISTS proveedores (
    id SERIAL PRIMARY KEY,
    
    -- Información Básica
    razon_social VARCHAR(200) NOT NULL,
    nombre_comercial VARCHAR(200),
    ruc VARCHAR(11) UNIQUE NOT NULL,
    
    -- Información de Contacto
    direccion TEXT,
    telefono VARCHAR(20),
    celular VARCHAR(20),
    email VARCHAR(100),
    web VARCHAR(150),
    
    -- Persona de Contacto
    contacto_nombre VARCHAR(100),
    contacto_cargo VARCHAR(50),
    contacto_telefono VARCHAR(20),
    contacto_email VARCHAR(100),
    
    -- Información Comercial
    tipo_proveedor VARCHAR(50) DEFAULT 'general' CHECK (tipo_proveedor IN ('general', 'nacional', 'internacional', 'servicio', 'producto')),
    categoria VARCHAR(50), -- alimentos, bebidas, suministros, servicios, etc.
    condicion_pago VARCHAR(50) DEFAULT 'contado', -- contado, crédito, mixto
    dias_credito INT DEFAULT 0,
    descuento_general NUMERIC(5,2) DEFAULT 0.00,
    
    -- Información Bancaria
    banco VARCHAR(100),
    cuenta_bancaria VARCHAR(50),
    tipo_cuenta VARCHAR(20), -- ahorros, corriente
    cci VARCHAR(50),
    
    -- Documentos y Certificaciones
    tiene_licencia BOOLEAN DEFAULT false,
    numero_licencia VARCHAR(50),
    fecha_venc_licencia DATE,
    tiene_certificado_calidad BOOLEAN DEFAULT false,
    tiene_certificado_sanitario BOOLEAN DEFAULT false,
    
    -- Control y Estado
    estado VARCHAR(20) DEFAULT 'activo' CHECK (estado IN ('activo', 'inactivo', 'bloqueado')),
    calificacion INT DEFAULT 5 CHECK (calificacion >= 1 AND calificacion <= 5),
    observaciones TEXT,
    
    -- Estadísticas
    total_compras NUMERIC(12,2) DEFAULT 0.00,
    total_deuda NUMERIC(12,2) DEFAULT 0.00,
    ultima_compra DATE,
    
    -- Auditoría
    usuario_creacion VARCHAR(50),
    fecha_creacion TIMESTAMP DEFAULT NOW(),
    usuario_modificacion VARCHAR(50),
    fecha_modificacion TIMESTAMP,
    
    CONSTRAINT check_ruc_valido CHECK (LENGTH(ruc) = 11)
);

-- Índices para mejorar el rendimiento
CREATE INDEX IF NOT EXISTS idx_proveedores_ruc ON proveedores(ruc);
CREATE INDEX IF NOT EXISTS idx_proveedores_razon_social ON proveedores(razon_social);
CREATE INDEX IF NOT EXISTS idx_proveedores_estado ON proveedores(estado);
CREATE INDEX IF NOT EXISTS idx_proveedores_tipo ON proveedores(tipo_proveedor);
CREATE INDEX IF NOT EXISTS idx_proveedores_categoria ON proveedores(categoria);

-- Tabla de Productos por Proveedor (Relación Muchos a Muchos)
CREATE TABLE IF NOT EXISTS proveedor_productos (
    id SERIAL PRIMARY KEY,
    proveedor_id INT NOT NULL REFERENCES proveedores(id) ON DELETE CASCADE,
    producto_id INT NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
    
    -- Información de Compra
    precio_compra NUMERIC(12,2),
    precio_negociado NUMERIC(12,2),
    descuento_producto NUMERIC(5,2) DEFAULT 0.00,
    
    -- Información de Entrega
    tiempo_entrega_dias INT DEFAULT 0,
    cantidad_minima INT DEFAULT 1,
    
    -- Control
    es_principal BOOLEAN DEFAULT false, -- Si es el proveedor principal de este producto
    estado VARCHAR(20) DEFAULT 'activo',
    fecha_registro TIMESTAMP DEFAULT NOW(),
    
    UNIQUE(proveedor_id, producto_id)
);

CREATE INDEX IF NOT EXISTS idx_proveedor_productos_proveedor ON proveedor_productos(proveedor_id);
CREATE INDEX IF NOT EXISTS idx_proveedor_productos_producto ON proveedor_productos(producto_id);

-- Tabla de Órdenes de Compra (Opcional - para futuro módulo de compras)
CREATE TABLE IF NOT EXISTS ordenes_compra (
    id SERIAL PRIMARY KEY,
    
    -- Relaciones
    proveedor_id INT NOT NULL REFERENCES proveedores(id),
    usuario_id INT REFERENCES usuarios(id),
    
    -- Información de la Orden
    numero_orden VARCHAR(50) UNIQUE NOT NULL,
    tipo_orden VARCHAR(20) DEFAULT 'compra' CHECK (tipo_orden IN ('compra', 'servicio', 'importacion')),
    fecha_orden DATE DEFAULT CURRENT_DATE,
    fecha_entrega_esperada DATE,
    fecha_entrega_real DATE,
    
    -- Montos
    subtotal NUMERIC(12,2) DEFAULT 0.00,
    igv NUMERIC(12,2) DEFAULT 0.00,
    descuento NUMERIC(12,2) DEFAULT 0.00,
    otros_cargos NUMERIC(12,2) DEFAULT 0.00,
    total NUMERIC(12,2) DEFAULT 0.00,
    
    -- Estado y Control
    estado VARCHAR(30) DEFAULT 'pendiente' CHECK (estado IN ('pendiente', 'aprobada', 'en_proceso', 'recibida', 'parcial', 'cancelada')),
    condicion_pago VARCHAR(50),
    observaciones TEXT,
    
    -- Auditoría
    fecha_creacion TIMESTAMP DEFAULT NOW(),
    fecha_modificacion TIMESTAMP,
    usuario_modificacion VARCHAR(50)
);

CREATE INDEX IF NOT EXISTS idx_ordenes_compra_proveedor ON ordenes_compra(proveedor_id);
CREATE INDEX IF NOT EXISTS idx_ordenes_compra_numero ON ordenes_compra(numero_orden);
CREATE INDEX IF NOT EXISTS idx_ordenes_compra_estado ON ordenes_compra(estado);
CREATE INDEX IF NOT EXISTS idx_ordenes_compra_fecha ON ordenes_compra(fecha_orden);

-- Tabla de Detalle de Órdenes de Compra
CREATE TABLE IF NOT EXISTS orden_compra_detalle (
    id SERIAL PRIMARY KEY,
    orden_compra_id INT NOT NULL REFERENCES ordenes_compra(id) ON DELETE CASCADE,
    producto_id INT NOT NULL REFERENCES productos(id),
    
    -- Cantidades
    cantidad_solicitada NUMERIC(12,2) NOT NULL,
    cantidad_recibida NUMERIC(12,2) DEFAULT 0.00,
    
    -- Precios
    precio_unitario NUMERIC(12,2) NOT NULL,
    descuento NUMERIC(12,2) DEFAULT 0.00,
    subtotal NUMERIC(12,2) NOT NULL,
    
    -- Control
    estado VARCHAR(20) DEFAULT 'pendiente' CHECK (estado IN ('pendiente', 'recibido', 'parcial', 'cancelado')),
    observaciones TEXT
);

CREATE INDEX IF NOT EXISTS idx_orden_detalle_orden ON orden_compra_detalle(orden_compra_id);
CREATE INDEX IF NOT EXISTS idx_orden_detalle_producto ON orden_compra_detalle(producto_id);

-- Tabla de Historial de Compras a Proveedores
CREATE TABLE IF NOT EXISTS historial_compras_proveedor (
    id SERIAL PRIMARY KEY,
    proveedor_id INT NOT NULL REFERENCES proveedores(id),
    orden_compra_id INT REFERENCES ordenes_compra(id),
    
    -- Información de la Compra
    fecha_compra DATE DEFAULT CURRENT_DATE,
    numero_documento VARCHAR(50),
    tipo_documento VARCHAR(20), -- factura, boleta, guia
    
    -- Montos
    total NUMERIC(12,2) NOT NULL,
    total_pagado NUMERIC(12,2) DEFAULT 0.00,
    saldo NUMERIC(12,2) DEFAULT 0.00,
    
    -- Estado
    estado_pago VARCHAR(20) DEFAULT 'pendiente' CHECK (estado_pago IN ('pendiente', 'parcial', 'pagado', 'vencido')),
    fecha_vencimiento DATE,
    
    -- Auditoría
    usuario_registro VARCHAR(50),
    fecha_registro TIMESTAMP DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_historial_proveedor ON historial_compras_proveedor(proveedor_id);
CREATE INDEX IF NOT EXISTS idx_historial_fecha ON historial_compras_proveedor(fecha_compra);

-- Comentarios en las tablas
COMMENT ON TABLE proveedores IS 'Tabla principal de proveedores del sistema';
COMMENT ON TABLE proveedor_productos IS 'Relación entre proveedores y productos que suministran';
COMMENT ON TABLE ordenes_compra IS 'Órdenes de compra realizadas a proveedores';
COMMENT ON TABLE orden_compra_detalle IS 'Detalle de productos en cada orden de compra';
COMMENT ON TABLE historial_compras_proveedor IS 'Historial de compras realizadas a proveedores';

-- Datos de ejemplo (opcional)
-- INSERT INTO proveedores (razon_social, nombre_comercial, ruc, direccion, telefono, email, tipo_proveedor, categoria, usuario_creacion)
-- VALUES 
--     ('GLORIA S.A.', 'Gloria', '20100190797', 'Av. Republica de Panama 2461, La Victoria, Lima', '014191919', 'ventas@gloria.com.pe', 'nacional', 'bebidas', 'admin'),
--     ('ALICORP S.A.A.', 'Alicorp', '20100055237', 'Av. Argentina 4793, Callao', '013156000', 'contacto@alicorp.com.pe', 'nacional', 'alimentos', 'admin'),
--     ('DISTRIBUIDORA PERU SAC', 'DistriPeru', '20123456789', 'Jr. Los Pinos 123, San Miguel, Lima', '014567890', 'ventas@distriperu.com', 'general', 'suministros', 'admin');

