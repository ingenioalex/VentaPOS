-- sudo -i -u postgres
-- psql
-- ------------
-- #>sudo apt update
-- #>sudo apt install postgresql postgresql-contrib
-- Luego inicia el servicio:

--  #>sudo systemctl start postgresql
-- #>sudo systemctl enable postgresql

-- Crea una base de datos y un usuario

CREATE USER rms_user WITH PASSWORD 'vl110185';
CREATE DATABASE rms_db OWNER rms_user;
GRANT ALL PRIVILEGES ON DATABASE rms_db TO rms_user;
\q
exit
-- 
-- guarda inicio de sesion y cierre sesion

CREATE TABLE sesiones (                                                                                id SERIAL PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL, 
    terminal VARCHAR(100) NOT NULL,
    local VARCHAR(100) NOT NULL,
    tipo_conexion VARCHAR(20) CHECK(tipo_conexion IN('login','logout','test')), -- test cuando inicia usuario test
    tiempo_conexion NUMERIC(12,2) DEFAULT 0, -- solo si es logout calcula tiempo conexion... por trabajar
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- usuarios ('admin', 'cajero','jefe','supervisor','tesorero','test')
CREATE TABLE usuarios (                                                                                id SERIAL PRIMARY KEY,
    nombre VARCHAR(100),
    email VARCHAR(100) UNIQUE, 
    usuario TEXT NOT NULL,
    password TEXT NOT NULL,
    rol VARCHAR(20) CHECK (rol IN ('admin', 'cajero','jefe','supervisor','tesorero','test')),
    conectado VARCHAR(20) CHECK(conectado IN('si','no','test')),
    estado BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- clientes
CREATE TABLE clientes (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100),
    documento_tipo VARCHAR(10) CHECK (documento_tipo IN ('DNI', 'RUC')),
    documento_numero VARCHAR(20) UNIQUE,
    direccion TEXT NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla departamento (1er nivel)
CREATE TABLE departamento (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

-- Tabla subdepartamento (2do nivel)
CREATE TABLE subdepartamento (
    id SERIAL PRIMARY KEY,
    departamento_id INT REFERENCES departamento(id),
    nombre VARCHAR(100) NOT NULL
);

-- Tabla clase (3er nivel)
CREATE TABLE clase (
    id SERIAL PRIMARY KEY,
    subdepartamento_id INT REFERENCES subdepartamento(id),
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE productos (
    id SERIAL PRIMARY KEY,
    descripcion_larga TEXT,
    descripcion_corta VARCHAR(10),
    sku VARCHAR(10) UNIQUE,
    nombre TEXT,  -- sku + descripcion
    codigo_barra VARCHAR(50) UNIQUE,
    plu VARCHAR(5) NULL,
    costo_compra NUMERIC(12,2) DEFAULT 0, 
    costo_promedio NUMERIC(12,2) DEFAULT 0,
    costo_reposicion NUMERIC(12,2) DEFAULT 0,
    precio_regular NUMERIC(12,2) DEFAULT 0,
    valor_venta NUMERIC(12,2) DEFAULT 0,
    precio_venta NUMERIC(12,2) DEFAULT 0,
    activo_promocion BOOLEAN DEFAULT FALSE,
    precio_promocion NUMERIC(12,2) DEFAULT 0, 
    descuento_porcentaje NUMERIC(10,2) NULL,
    afecto_igv BOOLEAN DEFAULT TRUE,
    valor_igv NUMERIC(12,2) DEFAULT 0,  
    stock_contable NUMERIC(12,2) DEFAULT 1,
    stock_disponible NUMERIC(12,2) DEFAULT 1,
    stock_minimo INT DEFAULT 1,
    venta_promedio NUMERIC(10,2) DEFAULT 0,
    pesable BOOLEAN DEFAULT TRUE,
    unidad_medida VARCHAR(20) NULL,
    marca VARCHAR(50) NULL,
    registro_sanitario VARCHAR(50) NULL,
    dias_retiro INT NULL,
    case_pack INT DEFAULT 1,
    tipo_producto VARCHAR(50) NULL,
    categoria VARCHAR(50) NULL,
    clase VARCHAR(50) NULL,
    clase_id INT REFERENCES clase(id),
    referencia VARCHAR(50) NULL,
    fecha_uingreso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    campo1 VARCHAR(50) NULL,
    campo2 VARCHAR(50) NULL,
    campo3 VARCHAR(50) NULL,
    estado BOOLEAN DEFAULT TRUE,
    tipo_estado VARCHAR(15) CHECK (tipo_estado IN ('activo', 'inactivo','descontinuado')),
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_actualizacion VARCHAR(20) NULL,
    fecha_creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- detalle modificacion precio producto
CREATE TABLE producto_precio (
    id SERIAL PRIMARY KEY,
    producto_id INT REFERENCES productos(id),
    descripcion_larga TEXT,
    costo_anterior NUMERIC(12,2) DEFAULT 0, 
    costo_nuevo NUMERIC(12,2) DEFAULT 0, 
    costo_promedio NUMERIC(12,2) DEFAULT 0,
    valor_venta_anterior NUMERIC(12,2) DEFAULT 0,
    precio_venta_anterior NUMERIC(12,2) DEFAULT 0,
    valor_venta_nuevo NUMERIC(12,2) DEFAULT 0,
    precio_venta_nuevo NUMERIC(12,2) DEFAULT 0,
    usuario_actualizacion VARCHAR(20) NULL,
    fecha_creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- EL terminal debe validarse Al inicio del programa CON UN Archivo de configuración local
--  Crea un archivo config.json en cada terminal con su codigo:
-- json
-- { "terminal": "CAJA01" }
-- ese archivo se valida con la data del tabla terminal, si esta activo

 CREATE TABLE terminal (
    id_terminal SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,       -- Ej: "Caja 01", "Mostrador Principal"
    tipo_comprobante VARCHAR(10) CHECK (tipo_comprobante IN ('B', 'F','NC','GR','NP','RE')),
    serie VARCHAR(10) NULL,  -- segun tipo de comprobante ejem F01 hace referencia factura en caja 01 , B03   refiere a boleta cajas 03   
    numero VARCHAR(30) NULL,  -- este debe actualizarse con un tigger despues de registro de comprobante venta
    serie_impresora VARCHAR(50) NULL,          -- Modelo: "TSP100", "TM-T20II"
    modo_impresora VARCHAR(20) NULL,           -- Ej: "ESC/POS", "Star Line"
    config_puerto VARCHAR(50) NULL,            -- Ej: "USB", "COM1:9600,N,8,1"
    direccion_ip VARCHAR(15) NULL,             -- Para impresoras de red
    activo BOOLEAN DEFAULT TRUE,
    local VARCHAR(20) NULL                -- Ej: "santa victoria - Área Ventas"
);

CREATE TABLE config_impresora (
    id_config SERIAL PRIMARY KEY,
    terminal_id INT REFERENCES terminal(id_terminal),
    margen_izquierdo INT DEFAULT 0,
    margen_derecho INT DEFAULT 0,
    caracteres_por_linea INT DEFAULT 42,  -- 40-80 según modelo
    codificacion VARCHAR(20) DEFAULT 'CP437' -- Europa: CP850, Latinoamérica: CP858
);
 
-- Comprobantes (boleta, factura, nota credito, guia remision, nota de pedido,retiro efectivo)

CREATE TABLE comprobantes (
    id SERIAL PRIMARY KEY,
    tipo VARCHAR(10) CHECK (tipo IN ('B', 'F','NC','GR','NP','RE')),
    serie VARCHAR(10) NULL,
    numero VARCHAR(30) NULL,  -- de la tabla terminal debe jalar numero correlativo serie segun el tipo de comprobante
    fecha_emision TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_pago TIMESTAMP NULL,
    fecha_proceso TIMESTAMP NULL,
    cliente_id INT REFERENCES clientes(id) NULL,
    dni VARCHAR(10) NULL,
    usuario_id INT REFERENCES usuarios(id),
    medio_pago VARCHAR(30) CHECK (medio_pago IN ('efectivo', 'tarjeta','yape','plin','otros')), --
    efectivo NUMERIC(12,2) DEFAULT 0,
    tarjeta NUMERIC(12,2) DEFAULT 0,
    otro_pago NUMERIC(12,2) DEFAULT 0,
    subtotal_afecto NUMERIC(12,2) DEFAULT 0,
    subtotal_inafecto NUMERIC(12,2) DEFAULT 0,
    subtotal NUMERIC(12,2) DEFAULT 0,
    valor_igv NUMERIC(12,2) DEFAULT 0,
    descto NUMERIC(12,2) DEFAULT 0,
    numero_item INT DEFAULT 0,
    numero_item_descto INT DEFAULT 0,
    total NUMERIC(12,2) DEFAULT 0,
    estado VARCHAR(20) DEFAULT 'emitido', -- emitido, anulado, test
    observaciones TEXT,
    vuelto NUMERIC(10,2) DEFAULT 0,
    sunat_codigo_respuesta VARCHAR(20) NULL,
    sunat_mensaje TEXT NULL,
    tipo_cierre VARCHAR(1) CHECK (tipo_cierre IN ('X', 'Z')) NULL, -- X: parcial, Z: definitivo
    cierre_id INT NULL,
    terminal_id INT REFERENCES terminal(id_terminal)
);

-- detalle del comprobante 
CREATE TABLE detalle_comprobante (
    id SERIAL PRIMARY KEY,
    comprobante_id INT REFERENCES comprobantes(id) ON DELETE CASCADE,
    producto_id INT REFERENCES productos(id),
    descripcion_producto VARCHAR(100) NULL,
    cantidad NUMERIC(10,2),
    precio_unitario NUMERIC(12,2),
    igv NUMERIC(12,2),
    subtotal NUMERIC(12,2),
    descto NUMERIC(12,2) DEFAULT 0,
    tipo_afectacion VARCHAR(20),
    estado VARCHAR(20) DEFAULT 'emitido' -- emitido, anulado, test
);
-- el tigger debe comprobar si es venta , y no registrar si es retiro efectivo
CREATE TABLE venta (
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

CREATE TABLE cierre_caja (
    id SERIAL PRIMARY KEY,
    tipo_cierre VARCHAR(1) CHECK (tipo_cierre IN ('X', 'Z')), -- X: parcial, Z: definitivo
    fecha_inicio TIMESTAMP NOT NULL, -- Fecha/hora del último cierre Z o X
    fecha_fin TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Fecha/hora del cierre actual
    total_ventas NUMERIC(12,2) DEFAULT 0, -- cantidad transacciones
    ticket_promedio NUMERIC(12,2) DEFAULT 0, -- cantidad Tp venta 
    total_efectivo NUMERIC(12,2) DEFAULT 0,
    total_tarjeta NUMERIC(12,2) DEFAULT 0,
    total_otros_pagos NUMERIC(12,2) DEFAULT 0,
    total_igv NUMERIC(12,2) DEFAULT 0,
    id_usuario INT, -- Cajero responsable
    observaciones TEXT -- incluir id supervisor 
);

-- el sistema debe permitir realizar un retiro de efectivo cuando tiene mas de 2000 soles en caja , se registra el retiro en bd y genera un tigger hacia tabla comprobante, sin detalle.

CREATE TABLE retiro_efectivo (
    id SERIAL PRIMARY KEY,
    terminal_id INT REFERENCES terminal(id_terminal),
    id_usuario INT,                         -- Cajero que realiza el retiro
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    monto NUMERIC(12,2) NOT NULL,
    motivo VARCHAR(100),                    -- Ej: "Pickup por exceso de efectivo"
    observaciones TEXT,
    id_cierre_caja INT REFERENCES cierre_caja(id), -- Opcional: asociar a un cierre si aplica
    autorizado_por VARCHAR(100)             -- Nombre o ID del supervisor autorizante
);




-- Movimientos de inventario  cuando retiras productos x venta deberia activarse un tigger y registra el movimiento por venta 
CREATE TABLE inventario_movimiento (
    id SERIAL PRIMARY KEY,
    producto_id INT REFERENCES productos(id),
    tipo_movimiento VARCHAR(20) CHECK (tipo_movimiento IN ('entrada', 'salida', 'venta')),
    cantidad NUMERIC(10,2),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    motivo TEXT,
    usuario_id INT REFERENCES usuarios(id),
    terminal_id INT REFERENCES terminal(id_terminal),
    comprobante_id INT REFERENCES comprobantes(id) NULL
);


--  trigger en PostgreSQL para calcularlos automáticamente a partir de fecha_venta.

CREATE OR REPLACE FUNCTION set_fecha_venta_info()
RETURNS TRIGGER AS $$
BEGIN
    NEW.anio := EXTRACT(YEAR FROM NEW.fecha_venta)::INT;
    NEW.mes := EXTRACT(MONTH FROM NEW.fecha_venta)::INT;
    NEW.mes_anio := (NEW.anio * 100) + NEW.mes;
    NEW.dia := EXTRACT(DAY FROM NEW.fecha_venta)::INT;
    NEW.semana := EXTRACT(WEEK FROM NEW.fecha_venta)::INT; -- Semana ISO (1-53)
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_set_fecha_venta_info
BEFORE INSERT ON venta
FOR EACH ROW
EXECUTE FUNCTION set_fecha_venta_info();

-- ---- TIGGER-----

-- FUNCIÓN para disminuir stock y registrar movimiento
CREATE OR REPLACE FUNCTION fn_restar_stock()
RETURNS TRIGGER AS $$
BEGIN
    -- Actualizar stock del producto
    UPDATE productos
    SET stock_disponible = stock_disponible - NEW.cantidad
    WHERE id = NEW.producto_id;

    -- Registrar movimiento de inventario
    INSERT INTO inventario_movimiento (
        producto_id, tipo_movimiento, cantidad,
        motivo, usuario_id, terminal_id, comprobante_id
    )
    VALUES (
        NEW.producto_id, 'salida', NEW.cantidad,
        'Venta', NEW.usuario_id, NEW.terminal_id, NEW.comprobante_id
    );

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- TRIGGER sobre tabla venta -- ----
CREATE TRIGGER trg_restar_stock
AFTER INSERT ON venta
FOR EACH ROW
EXECUTE FUNCTION fn_restar_stock();

-- ----- - - - - - 
-- FUNCIÓN para crear comprobante automático por retiro
CREATE OR REPLACE FUNCTION fn_generar_comprobante_retiro()
RETURNS TRIGGER AS $$
DECLARE
    nueva_serie TEXT;
    nuevo_numero TEXT;
    id_terminal INT;
BEGIN
    -- Obtener datos actuales de la terminal
    SELECT serie, numero, id_terminal INTO nueva_serie, nuevo_numero, id_terminal
    FROM terminal
    WHERE id_terminal = NEW.terminal_id;

    -- Insertar comprobante de tipo "RE" (retiro)
    INSERT INTO comprobantes (
        tipo, serie, numero, fecha_emision,
        usuario_id, medio_pago, total, terminal_id, observaciones
    )
    VALUES (
        'RE', nueva_serie, nuevo_numero, CURRENT_TIMESTAMP,
        NEW.id_usuario, 'efectivo', NEW.monto, NEW.terminal_id, 'Retiro de efectivo'
    );

    -- Actualizar número correlativo en terminal
    UPDATE terminal
    SET numero = (CAST(numero AS INTEGER) + 1)::TEXT
    WHERE id_terminal = NEW.terminal_id;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- TRIGGER al insertar retiro
CREATE TRIGGER trg_comprobante_retiro
AFTER INSERT ON retiro_efectivo
FOR EACH ROW
WHEN (NEW.monto > 2000)
EXECUTE FUNCTION fn_generar_comprobante_retiro();

-- -------

-- FUNCIÓN para actualizar número correlativo en terminal
CREATE OR REPLACE FUNCTION fn_actualizar_correlativo_terminal()
RETURNS TRIGGER AS $$
BEGIN
    -- Solo actualizar si el comprobante no es tipo 'RE' (retirado automáticamente por trigger anterior)
    IF NEW.tipo <> 'RE' THEN
        UPDATE terminal
        SET numero = (CAST(numero AS INTEGER) + 1)::TEXT
        WHERE id_terminal = NEW.terminal_id;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- TRIGGER al insertar comprobante
CREATE TRIGGER trg_actualizar_correlativo
AFTER INSERT ON comprobantes
FOR EACH ROW
EXECUTE FUNCTION fn_actualizar_correlativo_terminal();


-- vista
-- VISTA para reporte Z diario por terminal
CREATE OR REPLACE VIEW vista_reporte_z AS
SELECT
    v.terminal_id,
    t.nombre_terminal,
    v.id_usuario,
    u.nombre AS cajero,
    DATE(v.fecha_venta) AS fecha,
    COUNT(v.id) AS cantidad_ventas,
    SUM(v.total) AS total_vendido,
    SUM(v.igv) AS total_igv,
    SUM(CASE WHEN v.medio_pago = 'efectivo' THEN v.total ELSE 0 END) AS total_efectivo,
    SUM(CASE WHEN v.medio_pago = 'tarjeta' THEN v.total ELSE 0 END) AS total_tarjeta
FROM ventas v
JOIN usuarios u ON u.id = v.id_usuario
JOIN terminal t ON t.id_terminal = v.terminal_id
GROUP BY v.terminal_id, t.nombre_terminal, v.id_usuario, u.nombre, DATE(v.fecha_venta)
ORDER BY fecha DESC;

-- vista de cierre cajas 
CREATE OR REPLACE VIEW vista_detalle_cierre_caja AS
SELECT
    cc.id,
    cc.tipo_cierre,
    cc.fecha_inicio,
    cc.fecha_fin,
    cc.total_ventas,
    cc.ticket_promedio,
    cc.total_efectivo,
    cc.total_tarjeta,
    cc.total_otros_pagos,
    cc.total_igv,
    cc.observaciones,
    cc.id_usuario,
    u.nombre AS cajero,
    t.nombre_terminal
FROM cierre_caja cc
JOIN usuarios u ON cc.id_usuario = u.id
JOIN terminal t ON t.id_terminal = (
    SELECT v.terminal_id FROM ventas v
    WHERE v.id_usuario = cc.id_usuario
      AND v.fecha_venta BETWEEN cc.fecha_inicio AND cc.fecha_fin
    LIMIT 1
)
ORDER BY cc.fecha_fin DESC;
-- ejemplo de consulta desde cualquier sistemas
SELECT * FROM vista_detalle_cierre_caja WHERE tipo_cierre = 'Z' LIMIT 5;


-- Usaremos jsonb_build_object() para retornar el detalle de un cierre por ID.

CREATE OR REPLACE FUNCTION fn_exportar_cierre_json(p_cierre_id INT)
RETURNS JSONB AS $$
BEGIN
    RETURN (
        SELECT jsonb_build_object(
            'id', cc.id,
            'tipo_cierre', cc.tipo_cierre,
            'fecha_inicio', cc.fecha_inicio,
            'fecha_fin', cc.fecha_fin,
            'total_ventas', cc.total_ventas,
            'ticket_promedio', cc.ticket_promedio,
            'total_efectivo', cc.total_efectivo,
            'total_tarjeta', cc.total_tarjeta,
            'total_otros_pagos', cc.total_otros_pagos,
            'total_igv', cc.total_igv,
            'id_usuario', cc.id_usuario,
            'observaciones', cc.observaciones,
            'cajero', u.nombre
        )
        FROM cierre_caja cc
        JOIN usuarios u ON cc.id_usuario = u.id
        WHERE cc.id = p_cierre_id
    );
END;
$$ LANGUAGE plpgsql;
-- ejemplo  : 
SELECT fn_exportar_cierre_json(10);



