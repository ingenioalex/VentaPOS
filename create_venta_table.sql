-- Crear tabla venta para registro detallado de ventas
CREATE TABLE IF NOT EXISTS venta (
    id SERIAL PRIMARY KEY,
    comprobante_id INT REFERENCES comprobantes(id),
    producto_id INT REFERENCES productos(id),
    nombre_producto VARCHAR(100) NULL,
    cantidad NUMERIC(10,2),
    precio_unitario NUMERIC(12,2),
    igv NUMERIC(12,2),
    subtotal NUMERIC(12,2),
    descto NUMERIC(12,2) DEFAULT 0,
    total NUMERIC(12,2),
    fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cliente VARCHAR(100),
    dni VARCHAR(15),
    medio_pago VARCHAR(30),
    terminal_id INT,
    usuario_id INT,
    anio INT,                -- Ejemplo: 2025
    mes INT,                 -- Ejemplo: 1 (enero), 12 (diciembre)
    mes_anio INT,            -- Ejemplo: 202501 (enero 2025)
    dia INT,                 -- Día del mes (1-31)
    semana INT               -- Semana del año (1-56)
);

-- Crear índices para mejorar el rendimiento
CREATE INDEX IF NOT EXISTS idx_venta_comprobante_id ON venta(comprobante_id);
CREATE INDEX IF NOT EXISTS idx_venta_fecha_venta ON venta(fecha_venta);
CREATE INDEX IF NOT EXISTS idx_venta_mes_anio ON venta(mes_anio);
CREATE INDEX IF NOT EXISTS idx_venta_usuario_id ON venta(usuario_id);
CREATE INDEX IF NOT EXISTS idx_venta_terminal_id ON venta(terminal_id);

