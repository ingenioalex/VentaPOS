-- Crear tabla cierres_caja si no existe
CREATE TABLE IF NOT EXISTS cierres_caja (
    id SERIAL PRIMARY KEY,
    fecha_cierre TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT REFERENCES users(id),
    terminal_id INT,
    monto_inicial NUMERIC(12,2) DEFAULT 0,
    monto_final NUMERIC(12,2) DEFAULT 0,
    total_ventas NUMERIC(12,2) DEFAULT 0,
    total_efectivo NUMERIC(12,2) DEFAULT 0,
    total_tarjeta NUMERIC(12,2) DEFAULT 0,
    total_otros NUMERIC(12,2) DEFAULT 0,
    diferencia NUMERIC(12,2) DEFAULT 0,
    observaciones TEXT,
    estado VARCHAR(20) DEFAULT 'abierto' -- 'abierto', 'cerrado'
);

CREATE INDEX IF NOT EXISTS idx_cierres_caja_fecha ON cierres_caja(fecha_cierre);
CREATE INDEX IF NOT EXISTS idx_cierres_caja_usuario ON cierres_caja(usuario_id);
CREATE INDEX IF NOT EXISTS idx_cierres_caja_estado ON cierres_caja(estado);
