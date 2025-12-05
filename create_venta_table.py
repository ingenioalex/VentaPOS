#!/usr/bin/env python3
"""
Script para crear la tabla venta en la base de datos
"""

import psycopg2
import sys

def crear_tabla_venta():
    """Crea la tabla venta en la base de datos"""
    
    # Configuración de conexión
    config = {
        'host': 'localhost',
        'database': 'rms_db',
        'user': 'rms_user',
        'password': 'vl110185'
    }
    
    try:
        # Conectar a la base de datos
        conn = psycopg2.connect(**config)
        cur = conn.cursor()
        
        print("✅ Conectado a la base de datos")
        
        # SQL para crear la tabla venta
        sql_create_table = """
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
            anio INT,
            mes INT,
            mes_anio INT,
            dia INT,
            semana INT
        );
        """
        
        # Crear la tabla
        cur.execute(sql_create_table)
        print("✅ Tabla 'venta' creada/verificada")
        
        # Crear índices
        indices = [
            "CREATE INDEX IF NOT EXISTS idx_venta_comprobante_id ON venta(comprobante_id);",
            "CREATE INDEX IF NOT EXISTS idx_venta_fecha_venta ON venta(fecha_venta);",
            "CREATE INDEX IF NOT EXISTS idx_venta_mes_anio ON venta(mes_anio);",
            "CREATE INDEX IF NOT EXISTS idx_venta_usuario_id ON venta(usuario_id);",
            "CREATE INDEX IF NOT EXISTS idx_venta_terminal_id ON venta(terminal_id);"
        ]
        
        for indice in indices:
            cur.execute(indice)
        
        print("✅ Índices creados/verificados")
        
        # Commit de los cambios
        conn.commit()
        print("✅ Cambios confirmados en la base de datos")
        
        # Verificar que la tabla existe
        cur.execute("""
            SELECT table_name, column_name, data_type 
            FROM information_schema.columns 
            WHERE table_name = 'venta' 
            ORDER BY ordinal_position
        """)
        
        columnas = cur.fetchall()
        print(f"\n📋 Estructura de la tabla 'venta':")
        for col in columnas:
            print(f"   - {col[1]}: {col[2]}")
        
        print(f"\n✅ Tabla 'venta' creada exitosamente con {len(columnas)} columnas")
        
    except psycopg2.Error as e:
        print(f"❌ Error de base de datos: {e}")
        sys.exit(1)
    except Exception as e:
        print(f"❌ Error inesperado: {e}")
        sys.exit(1)
    finally:
        if 'cur' in locals():
            cur.close()
        if 'conn' in locals():
            conn.close()
            print("🔌 Conexión cerrada")

if __name__ == "__main__":
    print("🚀 Iniciando creación de tabla 'venta'...")
    crear_tabla_venta()
    print("\n🎉 Proceso completado exitosamente!")

