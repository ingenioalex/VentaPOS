-- Crear tabla de movimientos de stock
CREATE TABLE IF NOT EXISTS movimientos_stock (
    id SERIAL PRIMARY KEY,
    producto_id INT REFERENCES productos(id),
    tipo_movimiento VARCHAR(20) NOT NULL, -- 'entrada', 'salida', 'ajuste'
    cantidad NUMERIC(10,2) NOT NULL,
    stock_anterior NUMERIC(10,2) NOT NULL,
    stock_nuevo NUMERIC(10,2) NOT NULL,
    motivo VARCHAR(200),
    referencia VARCHAR(100), -- Número de comprobante, factura, etc.
    usuario_id INT,
    fecha_movimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    observaciones TEXT
);

-- Crear índices para mejorar el rendimiento
CREATE INDEX IF NOT EXISTS idx_movimientos_producto_id ON movimientos_stock(producto_id);
CREATE INDEX IF NOT EXISTS idx_movimientos_fecha ON movimientos_stock(fecha_movimiento);
CREATE INDEX IF NOT EXISTS idx_movimientos_tipo ON movimientos_stock(tipo_movimiento);
CREATE INDEX IF NOT EXISTS idx_movimientos_usuario_id ON movimientos_stock(usuario_id);

-- Crear tabla de alertas de stock
CREATE TABLE IF NOT EXISTS alertas_stock (
    id SERIAL PRIMARY KEY,
    producto_id INT REFERENCES productos(id),
    tipo_alerta VARCHAR(20) NOT NULL, -- 'stock_bajo', 'sin_stock', 'sobre_stock'
    stock_actual NUMERIC(10,2) NOT NULL,
    stock_minimo NUMERIC(10,2),
    fecha_alerta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    leida BOOLEAN DEFAULT FALSE,
    usuario_id INT
);

-- Crear índices para alertas
CREATE INDEX IF NOT EXISTS idx_alertas_producto_id ON alertas_stock(producto_id);
CREATE INDEX IF NOT EXISTS idx_alertas_fecha ON alertas_stock(fecha_alerta);
CREATE INDEX IF NOT EXISTS idx_alertas_leida ON alertas_stock(leida);

