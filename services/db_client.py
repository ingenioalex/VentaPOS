import psycopg2

from PySide6.QtCore import QTime, QDateTime

class DBClient:
    def __init__(self):
        self.conn = psycopg2.connect(
            host="localhost",
            database="rms_db",
            user="rms_user",
            password="vl110185"
        )

    def verificar_usuario(self, usuario, clave):
        with self.conn.cursor() as cur:
            cur.execute("SELECT password, rol, conectado FROM usuarios WHERE usuario = %s", (usuario,))
            row = cur.fetchone()
            if row:
                stored_clave, rol, conectado = row
                if stored_clave == clave:
                    if rol in ('cajero', 'barras') and conectado == 'si':
                        return 'ya_conectado'
                    self.set_estado_conectado(usuario, True)
                    return True
            return False

    def get_rol_usuario(self, usuario):
        with self.conn.cursor() as cur:
            cur.execute("SELECT rol FROM usuarios WHERE usuario = %s", (usuario,))
            row = cur.fetchone()
            return row[0] if row else None

    # ==================== CONFIGURACIÓN: USUARIOS/ROLES ====================
    def actualizar_rol_usuario(self, usuario_id: int, nuevo_rol: str) -> bool:
        """Actualizar el campo rol en tabla usuarios (valores válidos: 'admin','cajero','jefe','supervisor','tesorero','test','barras','generico','digitador')."""
        try:
            with self.conn.cursor() as cur:
                cur.execute(
                    """
                    UPDATE usuarios
                    SET rol = %s
                    WHERE id = %s
                    """,
                    (nuevo_rol, usuario_id),
                )
            self.conn.commit()
            return True
        except Exception as e:
            self.conn.rollback()
            print(f"Error actualizando rol de usuario: {e}")
            return False

    def actualizar_supervisor_usuario(self, usuario_id: int, es_supervisor: int) -> bool:
        """Actualizar flag supervisor (0/1) en tabla usuarios."""
        try:
            with self.conn.cursor() as cur:
                cur.execute(
                    """
                    UPDATE usuarios
                    SET supervisor = %s
                    WHERE id = %s
                    """,
                    (int(es_supervisor), usuario_id),
                )
            self.conn.commit()
            return True
        except Exception as e:
            self.conn.rollback()
            print(f"Error actualizando supervisor de usuario: {e}")
            return False

    def set_estado_conectado(self, usuario: str, conectado: bool) -> bool:
        """Actualizar campo conectado ('si'/'no'/'test') sin cambiar esquema."""
        try:
            valor = 'si' if conectado is True else ('no' if conectado is False else 'test')
            with self.conn.cursor() as cur:
                cur.execute(
                    """
                    UPDATE usuarios
                    SET conectado = %s
                    WHERE usuario = %s
                    """,
                    (valor, usuario),
                )
            self.conn.commit()
            return True
        except Exception as e:
            self.conn.rollback()
            print(f"Error actualizando estado conectado: {e}")
            return False

    def actualizar_conectado_usuario_por_id(self, usuario_id: int, valor: str) -> bool:
        """Actualizar 'conectado' por id con valor explícito ('si','no','test')."""
        try:
            with self.conn.cursor() as cur:
                cur.execute(
                    """
                    UPDATE usuarios
                    SET conectado = %s
                    WHERE id = %s
                    """,
                    (valor, usuario_id),
                )
            self.conn.commit()
            return True
        except Exception as e:
            self.conn.rollback()
            print(f"Error actualizando conectado por id: {e}")
            return False

    def get_usuarios(self, filtro=""):
        with self.conn.cursor() as cur:
            query = "SELECT id, nombre, usuario, rol, conectado, estado FROM usuarios"
            if filtro:
                query += " WHERE nombre ILIKE %s OR usuario ILIKE %s"
                cur.execute(query, (f"%{filtro}%", f"%{filtro}%"))
            else:
                cur.execute(query)
            return cur.fetchall()

    # ---------- NUEVO: obtener id de usuario por login ----------
    def get_usuario_id_por_login(self, usuario: str) -> int | None:
        with self.conn.cursor() as cur:
            cur.execute("SELECT id FROM usuarios WHERE usuario = %s", (usuario,))
            row = cur.fetchone()
            return row[0] if row else None

    # ---------- NUEVO: crear comprobante usando tabla terminal ----------
    def crear_comprobante_por_terminal(
        self,
        terminal_nombre: str,
        tipo: str,                 # 'B', 'F', 'RE', etc.
        usuario_id: int,
        medio_pago: str,
        cliente_id: int | None = None,
        dni: str | None = None,
    ) -> dict:
        try:
            with self.conn.cursor() as cur:
                # 1) Tomar serie/numero actuales de la terminal para ese tipo
                cur.execute("""
                    SELECT id_terminal, serie, COALESCE(numero,'0')
                    FROM terminal
                    WHERE nombre = %s AND tipo_comprobante = %s AND activo = TRUE
                    LIMIT 1
                """, (terminal_nombre, tipo))
                term = cur.fetchone()
                if not term:
                    raise ValueError(f"No existe terminal activa '{terminal_nombre}' para tipo {tipo}.")
                term_id, serie, numero_actual = term
                try:
                    next_n = int(numero_actual) + 1
                except Exception:
                    next_n = 1
                numero_str = f"{next_n:08d}"

                # 2) Crear comprobante con fechas
                from datetime import datetime
                fecha_actual = datetime.now()
                
                cur.execute("""
                    INSERT INTO comprobantes (
                        tipo, serie, numero, usuario_id, medio_pago,
                        cliente_id, dni, subtotal_afecto, subtotal_inafecto, subtotal,
                        valor_igv, descto, numero_item, numero_item_descto, total,
                        estado, terminal_id, fecha_emision, fecha_pago, fecha_proceso
                    ) VALUES (%s, %s, %s, %s, %s,
                              %s, %s, 0, 0, 0,
                              0, 0, 0, 0, 0,
                              'emitido', %s, %s, %s, %s)
                    RETURNING id
                """, (tipo, serie, numero_str, usuario_id, medio_pago, cliente_id, dni, term_id, fecha_actual, fecha_actual, fecha_actual))
                comp_id = cur.fetchone()[0]

                # 3) Avanzar numeración de la terminal (si luego pondrás trigger, igual es seguro)
                cur.execute("UPDATE terminal SET numero = %s WHERE id_terminal = %s", (numero_str, term_id))

            self.conn.commit()
            return {"id": comp_id, "serie": serie, "numero": numero_str}
        except Exception as e:
            self.conn.rollback()
            raise

        # Alias para compatibilidad con la UI actual
    def fn_crear_comprobante_por_terminal(
        self,
        terminal_nombre: str,
        tipo: str,
        usuario_id: int,
        medio_pago: str,
        cliente_id: int | None = None,
        dni: str | None = None,
    ):
        # Reutiliza la implementación existente
        return self.crear_comprobante_por_terminal(
            terminal_nombre=terminal_nombre,
            tipo=tipo,
            usuario_id=usuario_id,
            medio_pago=medio_pago,
            cliente_id=cliente_id,
            dni=dni,
        )


    # ---------- NUEVO: insertar detalle de comprobante ----------
    def insertar_detalle_comprobante(
        self,
        comprobante_id: int,
        producto_id: int,
        descripcion: str,
        cantidad: float,
        precio_unitario: float,        # precio con IGV si afecto
        igv_total: float,              # igv del ítem (cantidad * igv_unit)
        subtotal_total: float,         # base imponible del ítem (cantidad * valor_unit)
        descto: float,
        tipo_afectacion: str           # 'GRAVADO' | 'INAFECTO'
    ):
        with self.conn.cursor() as cur:
            cur.execute("""
                INSERT INTO detalle_comprobante (
                    comprobante_id, producto_id, descripcion_producto, cantidad,
                    precio_unitario, igv, subtotal, descto, tipo_afectacion, estado
                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, 'Activo')
            """, (comprobante_id, producto_id, descripcion, cantidad,
                  precio_unitario, igv_total, subtotal_total, descto, tipo_afectacion))
        self.conn.commit()

    # ---------- NUEVO: finalizar cabecera (pagos + totales + fecha_pago) ----------
    def finalizar_cabecera_comprobante(
        self,
        comprobante_id: int,
        efectivo: float,
        tarjeta: float,
        otros: float,
        total: float
    ):
        try:
            with self.conn.cursor() as cur:
                # Recalcular totales desde detalle
                cur.execute("""
                    SELECT 
                      COALESCE(SUM(CASE WHEN tipo_afectacion='GRAVADO'  THEN subtotal ELSE 0 END),0)  AS sub_afecto,
                      COALESCE(SUM(CASE WHEN tipo_afectacion='INAFECTO' THEN subtotal ELSE 0 END),0)  AS sub_inafecto,
                      COALESCE(SUM(igv),0) AS igv,
                      COALESCE(SUM(descto),0) AS descto,
                      COALESCE(SUM(precio_unitario * cantidad),0) AS total
                    FROM detalle_comprobante
                    WHERE comprobante_id = %s AND estado = 'Activo'
                """, (comprobante_id,))
                sub_af, sub_in, igv, descto, total_calc = cur.fetchone()

                # Si nos pasaron un total “forzado”, usa ese; si no, usa el calculado
                total_final = total if (total and total > 0) else total_calc

                # Contar número de items
                cur.execute("""
                    SELECT COUNT(*) as total_items,
                           COUNT(CASE WHEN descto > 0 THEN 1 END) as items_con_descuento
                    FROM detalle_comprobante
                    WHERE comprobante_id = %s AND estado = 'Activo'
                """, (comprobante_id,))
                total_items, items_con_descuento = cur.fetchone()

                cur.execute("""
                    UPDATE comprobantes
                    SET subtotal_afecto = %s,
                        subtotal_inafecto = %s,
                        subtotal = %s,
                        valor_igv = %s,
                        descto = %s,
                        total = %s,
                        efectivo = %s,
                        tarjeta = %s,
                        otro_pago = %s,
                        numero_item = %s,
                        numero_item_descto = %s,
                        fecha_pago = NOW(),
                        fecha_proceso = NOW()
                    WHERE id = %s
                """, (sub_af, sub_in, sub_af + sub_in, igv, descto, total_final,
                      efectivo, tarjeta, otros, total_items, items_con_descuento, comprobante_id))
            self.conn.commit()
        except Exception:
            self.conn.rollback()
            raise

    # ---------- NUEVO: supervisor / anulación / cierres / reimpresión ----------
    def validar_supervisor(self, usuario: str, clave: str) -> bool:
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT 1
                FROM usuarios
                WHERE usuario=%s AND password=%s AND rol IN ('admin','jefe','supervisor')
            """, (usuario, clave))
            return cur.fetchone() is not None

    def anular_comprobante(self, comprobante_id: int, supervisor_id: int, motivo: str):
        with self.conn.cursor() as cur:
            # marca cabecera
            cur.execute("""
                UPDATE comprobantes
                SET estado='anulado', observaciones = COALESCE(observaciones,'') || %s
                WHERE id=%s
            """, (f"\nAnulado por {supervisor_id}: {motivo}", comprobante_id))
            # marca detalle
            cur.execute("""
                UPDATE detalle_comprobante SET estado='Anulado' WHERE comprobante_id=%s
            """, (comprobante_id,))
        self.conn.commit()

    def resumen_cierre_x_usuario(self, usuario_id: int) -> dict:
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT 
                    COALESCE(SUM(total),0), 
                    COALESCE(SUM(efectivo),0),
                    COALESCE(SUM(tarjeta),0),
                    COALESCE(SUM(otro_pago),0),
                    COUNT(*)
                FROM comprobantes
                WHERE usuario_id=%s AND estado='emitido' 
                  AND date(fecha_emision) = CURRENT_DATE
            """, (usuario_id,))
            total, ef, tj, ot, cant = cur.fetchone()
            return {"total": float(total), "efectivo": float(ef), "tarjeta": float(tj), "otros": float(ot), "cant": int(cant)}

    def resumen_cierre_z_terminal(self, terminal_id: int) -> dict:
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT 
                    COALESCE(SUM(total),0),
                    COUNT(*)
                FROM comprobantes
                WHERE terminal_id=%s AND estado='emitido'
                  AND date(fecha_emision) = CURRENT_DATE
            """, (terminal_id,))
            total, cant = cur.fetchone()
            return {"total": float(total), "cant": int(cant)}

    def reimprimir_cabecera(self, comprobante_id: int) -> dict | None:
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT tipo, serie, numero FROM comprobantes WHERE id=%s
            """, (comprobante_id,))
            row = cur.fetchone()
            if not row:
                return None
            return {"tipo": row[0], "serie": row[1], "numero": row[2]}



    def agregar_usuario(self, nombre, usuario, clave, rol):
        with self.conn.cursor() as cur:
            cur.execute("INSERT INTO usuarios (nombre, usuario, password, rol, conectado) VALUES (%s, %s, %s, %s, 'no')",
                        (nombre, usuario, clave, rol))
            self.conn.commit()

    def editar_usuario(self, id_usuario, nombre, usuario, rol):
        with self.conn.cursor() as cur:
            cur.execute("UPDATE usuarios SET nombre = %s, usuario = %s, rol = %s, conectado = 'si', estado = TRUE WHERE id = %s",
                        (nombre, usuario, rol, id_usuario))
            self.conn.commit()

    def eliminar_usuario(self, id_usuario):
        with self.conn.cursor() as cur:
            cur.execute("DELETE FROM usuarios WHERE id = %s", (id_usuario,))
            self.conn.commit()

    def cambiar_contraseña_usuario(self, usuario_id, nueva_contraseña):
        """Cambiar la contraseña de un usuario (solo admin)"""
        try:
            with self.conn.cursor() as cur:
                cur.execute(
                    "UPDATE usuarios SET password = %s WHERE id = %s",
                    (nueva_contraseña, usuario_id)
                )
                self.conn.commit()
                return True
        except Exception as e:
            self.conn.rollback()
            print(f"Error cambiando contraseña: {e}")
            return False

    def set_estado_conectado(self, usuario, conectado):
        estado = 'si' if conectado else 'no'
        with self.conn.cursor() as cur:
            cur.execute("UPDATE usuarios SET conectado = %s WHERE usuario = %s", (estado, usuario))
            self.conn.commit()

    def get_departamentos(self):
        with self.conn.cursor() as cur:
            cur.execute("SELECT id, nombre FROM departamento ORDER BY id")
            return cur.fetchall()

    def get_subdepartamentos(self, departamento_id):
        with self.conn.cursor() as cur:
            cur.execute("SELECT id, nombre FROM subdepartamento WHERE departamento_id = %s ORDER BY id", (departamento_id,))
            return cur.fetchall()

    def get_clases(self, subdepartamento_id):
        with self.conn.cursor() as cur:
            cur.execute("SELECT id, nombre FROM clase WHERE subdepartamento_id = %s", (subdepartamento_id,))
            return cur.fetchall()

    def agregar_departamento(self, nombre):
        with self.conn.cursor() as cur:
            cur.execute("INSERT INTO departamento (nombre) VALUES (%s)", (nombre,))
            self.conn.commit()

    def agregar_subdepartamento(self, departamento_id, nombre):
        with self.conn.cursor() as cur:
            cur.execute("INSERT INTO subdepartamento (departamento_id, nombre) VALUES (%s, %s)", (departamento_id, nombre))
            self.conn.commit()

    def agregar_clase(self, subdepartamento_id, nombre):
        with self.conn.cursor() as cur:
            cur.execute("INSERT INTO clase (subdepartamento_id, nombre) VALUES (%s, %s)", (subdepartamento_id, nombre))
            self.conn.commit()

    def editar_departamento(self, id_, nombre):
        with self.conn.cursor() as cur:
            cur.execute("UPDATE departamento SET nombre = %s WHERE id = %s", (nombre, id_))
            self.conn.commit()

    def eliminar_departamento(self, id_):
        with self.conn.cursor() as cur:
            cur.execute("SELECT COUNT(*) FROM productos p JOIN clase c ON p.clase_id = c.id JOIN subdepartamento s ON c.subdepartamento_id = s.id WHERE s.departamento_id = %s", (id_,))
            if cur.fetchone()[0] > 0:
                return False
            cur.execute("DELETE FROM departamento WHERE id = %s", (id_,))
            self.conn.commit()
            return True

    def editar_subdepartamento(self, id_, nombre):
        with self.conn.cursor() as cur:
            cur.execute("UPDATE subdepartamento SET nombre = %s WHERE id = %s", (nombre, id_))
            self.conn.commit()

    def eliminar_subdepartamento(self, id_):
        with self.conn.cursor() as cur:
            cur.execute("DELETE FROM subdepartamento WHERE id = %s", (id_,))
            self.conn.commit()

    def editar_clase(self, id_, nombre):
        with self.conn.cursor() as cur:
            cur.execute("UPDATE clase SET nombre = %s WHERE id = %s", (nombre, id_))
            self.conn.commit()

    def eliminar_clase(self, id_):
        with self.conn.cursor() as cur:
            cur.execute("DELETE FROM clase WHERE id = %s", (id_,))
            self.conn.commit()

    def get_clases_detalle(self):
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT c.id, c.nombre AS clase, s.nombre AS subdepartamento, d.nombre AS departamento
                FROM clase c
                JOIN subdepartamento s ON c.subdepartamento_id = s.id
                JOIN departamento d ON s.departamento_id = d.id
            """)
            return cur.fetchall()

    def get_correlativo_sku(self):
        with self.conn.cursor() as cur:
            cur.execute("SELECT COALESCE(MAX(id), 0) + 1 FROM productos")
            row = cur.fetchone()
            return row[0] if row else 1
    
    def buscar_productos(self, texto):
        with self.conn.cursor() as cur:
            query = """
                SELECT 
                    id, sku, descripcion_larga, codigo_barra,
                    costo_compra, valor_venta, precio_venta, afecto_igv
                FROM productos
                WHERE 
                    sku ILIKE %s OR
                    descripcion_larga ILIKE %s OR
                    codigo_barra ILIKE %s
                ORDER BY descripcion_larga
                LIMIT 20
            """
            filtro = f"%{texto}%"
            cur.execute(query, (filtro, filtro, filtro))
            rows = cur.fetchall()
            columns = [desc[0] for desc in cur.description]
            return [dict(zip(columns, row)) for row in rows]

    def buscar_producto_por_marca(self, marca):
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT id, sku, descripcion_larga, codigo_barra, costo_compra,
                       costo_promedio, valor_venta, precio_venta, afecto_igv,
                       stock_contable, stock_disponible
                FROM productos
                WHERE marca ILIKE %s
            """, (f"%{marca}%",))
            rows = cur.fetchall()
            return [
                {
                    'id': r[0],
                    'sku': r[1],
                    'descripcion_larga': r[2],
                    'codigo_barra': r[3],
                    'costo_compra': r[4],
                    'costo_promedio': r[5],
                    'valor_venta': r[6],
                    'precio_venta': r[7],
                    'afecto_igv': r[8],
                    'stock_contable': r[9],
                    'stock_disponible': r[10]
                } for r in rows
            ]        

    def buscar_producto_por_sku(self, sku):
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT id, sku, descripcion_larga, codigo_barra, costo_compra,
                       costo_promedio, valor_venta, precio_venta, afecto_igv,
                       stock_contable, stock_disponible
                FROM productos
                WHERE sku ILIKE %s
            """, (f"%{sku}%",))
            rows = cur.fetchall()
            return [
                {
                    'id': r[0],
                    'sku': r[1],
                    'descripcion_larga': r[2],
                    'codigo_barra': r[3],
                    'costo_compra': r[4],
                    'costo_promedio': r[5],
                    'valor_venta': r[6],
                    'precio_venta': r[7],
                    'afecto_igv': r[8],
                    'stock_contable': r[9],
                    'stock_disponible': r[10]
                } for r in rows
            ]

    def buscar_producto_por_barra(self, barra):
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT id, sku, descripcion_larga, codigo_barra, costo_compra,
                       costo_promedio, valor_venta, precio_venta, afecto_igv,
                       stock_contable, stock_disponible
                FROM productos
                WHERE codigo_barra ILIKE %s
            """, (f"%{barra}%",))
            rows = cur.fetchall()
            return [
                {
                    'id': r[0],
                    'sku': r[1],
                    'descripcion_larga': r[2],
                    'codigo_barra': r[3],
                    'costo_compra': r[4],
                    'costo_promedio': r[5],
                    'valor_venta': r[6],
                    'precio_venta': r[7],
                    'afecto_igv': r[8],
                    'stock_contable': r[9],
                    'stock_disponible': r[10]
                } for r in rows
            ]

    def buscar_producto_por_descripcion(self, descripcion):
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT id, sku, descripcion_larga, codigo_barra, costo_compra,
                       costo_promedio, valor_venta, precio_venta, afecto_igv,
                       stock_contable, stock_disponible
                FROM productos
                WHERE descripcion_larga ILIKE %s
            """, (f"%{descripcion}%",))
            rows = cur.fetchall()
            return [
                {
                    'id': r[0],
                    'sku': r[1],
                    'descripcion_larga': r[2],
                    'codigo_barra': r[3],
                    'costo_compra': r[4],
                    'costo_promedio': r[5],
                    'valor_venta': r[6],
                    'precio_venta': r[7],
                    'afecto_igv': r[8],
                    'stock_contable': r[9],
                    'stock_disponible': r[10]
                } for r in rows
            ]

    def guardar_historial_precios(self, producto_id, descripcion_larga, costos):
        with self.conn.cursor() as cur:
            cur.execute("""
                INSERT INTO producto_precio (
                    producto_id, descripcion_larga,
                    costo_anterior, costo_nuevo, costo_promedio,
                    valor_venta_anterior, precio_venta_anterior,
                    valor_venta_nuevo, precio_venta_nuevo,
                    usuario_actualizacion
                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """, (
                producto_id, descripcion_larga,
                costos['costo_anterior'], costos['costo_nuevo'], costos['costo_promedio'],
                costos['valor_venta_anterior'], costos['precio_venta_anterior'],
                costos['valor_venta_nuevo'], costos['precio_venta_nuevo'],
                costos['usuario_actualizacion']
            ))
            self.conn.commit()

    def obtener_historial_precios(self, producto_id):
        """Obtiene el historial de precios de un producto desde la tabla producto_precio"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        fecha_creado,
                        usuario_actualizacion,
                        costo_anterior,
                        costo_nuevo,
                        valor_venta_anterior,
                        valor_venta_nuevo,
                        precio_venta_anterior,
                        precio_venta_nuevo
                    FROM producto_precio 
                    WHERE producto_id = %s 
                    ORDER BY fecha_creado DESC
                """, (producto_id,))
                return cur.fetchall()
        except Exception as e:
            print(f"Error al obtener historial de precios: {e}")
            return []

    # ========== MÉTODOS PARA GESTIÓN DE CLIENTES ==========
    
    def get_clientes(self, filtro=""):
        """Obtiene la lista de clientes con filtro opcional"""
        with self.conn.cursor() as cur:
            query = """
                SELECT id, documento_tipo, documento_numero, nombre, 
                       direccion, telefono, email, creado_en
                FROM clientes
            """
            if filtro:
                query += " WHERE nombre ILIKE %s OR documento_numero ILIKE %s OR email ILIKE %s"
                cur.execute(query, (f"%{filtro}%", f"%{filtro}%", f"%{filtro}%"))
            else:
                cur.execute(query)
            return cur.fetchall()

    def get_cliente_por_id(self, cliente_id):
        """Obtiene un cliente específico por su ID"""
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT id, documento_tipo, documento_numero, nombre, 
                       direccion, telefono, email, creado_en
                FROM clientes WHERE id = %s
            """, (cliente_id,))
            return cur.fetchone()

    def get_cliente_por_documento(self, documento_numero):
        """Obtiene un cliente por su número de documento"""
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT id, documento_tipo, documento_numero, nombre, 
                       direccion, telefono, email, creado_en
                FROM clientes WHERE documento_numero = %s
            """, (documento_numero,))
            return cur.fetchone()

    def agregar_cliente(self, documento_tipo, documento_numero, nombre, direccion="", telefono="", email=""):
        """Agrega un nuevo cliente"""
        with self.conn.cursor() as cur:
            cur.execute("""
                INSERT INTO clientes (documento_tipo, documento_numero, nombre, direccion, telefono, email)
                VALUES (%s, %s, %s, %s, %s, %s)
            """, (documento_tipo, documento_numero, nombre, direccion, telefono, email))
            self.conn.commit()

    def editar_cliente(self, cliente_id, documento_tipo, documento_numero, nombre, direccion="", telefono="", email=""):
        """Edita un cliente existente"""
        with self.conn.cursor() as cur:
            cur.execute("""
                UPDATE clientes 
                SET documento_tipo = %s, documento_numero = %s, nombre = %s, 
                    direccion = %s, telefono = %s, email = %s
                WHERE id = %s
            """, (documento_tipo, documento_numero, nombre, direccion, telefono, email, cliente_id))
            self.conn.commit()

    def eliminar_cliente(self, cliente_id):
        """Elimina un cliente (verifica si tiene compras asociadas)"""
        with self.conn.cursor() as cur:
            # Verificar si el cliente tiene compras asociadas
            cur.execute("SELECT COUNT(*) FROM comprobantes WHERE cliente_id = %s", (cliente_id,))
            if cur.fetchone()[0] > 0:
                return False  # No se puede eliminar si tiene compras
            
            cur.execute("DELETE FROM clientes WHERE id = %s", (cliente_id,))
            self.conn.commit()
            return True

    def get_historial_compras_cliente(self, cliente_id):
        """Obtiene el historial de compras de un cliente por ID"""
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT c.id, c.tipo, c.serie, c.numero, c.fecha_emision, 
                       c.total, c.medio_pago, u.nombre as cajero
                FROM comprobantes c
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.cliente_id = %s
                ORDER BY c.fecha_emision DESC
            """, (cliente_id,))
            return cur.fetchall()

    def get_historial_compras_cliente_por_documento(self, documento_numero):
        """Obtiene el historial de compras de un cliente por documento_numero"""
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT c.id, c.tipo, c.serie, c.numero, c.fecha_emision, 
                       c.total, c.medio_pago, u.nombre as cajero
                FROM comprobantes c
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.dni = %s
                ORDER BY c.fecha_emision DESC
            """, (documento_numero,))
            return cur.fetchall()

    def get_estadisticas_cliente(self, cliente_id):
        """Obtiene estadísticas de un cliente por ID"""
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT 
                    COUNT(*) as total_compras,
                    COALESCE(SUM(total), 0) as total_gastado,
                    COALESCE(AVG(total), 0) as promedio_compra,
                    MIN(fecha_emision) as primera_compra,
                    MAX(fecha_emision) as ultima_compra
                FROM comprobantes 
                WHERE cliente_id = %s
            """, (cliente_id,))
            return cur.fetchone()

    def get_estadisticas_cliente_por_documento(self, documento_numero):
        """Obtiene estadísticas de un cliente por documento_numero"""
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT 
                    COUNT(*) as total_compras,
                    COALESCE(SUM(total), 0) as total_gastado,
                    COALESCE(AVG(total), 0) as promedio_compra,
                    MIN(fecha_emision) as primera_compra,
                    MAX(fecha_emision) as ultima_compra
                FROM comprobantes 
                WHERE dni = %s
            """, (documento_numero,))
            return cur.fetchone()

    # ========== MÉTODOS PARA CIERRE DE CAJA ==========
    
    def get_ultimo_cierre_caja(self, usuario_id):
        """Obtiene el último cierre de caja de un usuario"""
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT id, tipo_cierre, fecha_inicio, fecha_fin, total_ventas,
                       ticket_promedio, total_efectivo, total_tarjeta, total_otros_pagos,
                       total_igv, observaciones
                FROM cierre_caja 
                WHERE id_usuario = %s 
                ORDER BY fecha_fin DESC 
                LIMIT 1
            """, (usuario_id,))
            return cur.fetchone()

    def get_ventas_desde_ultimo_cierre(self, usuario_id, fecha_desde):
        """Obtiene las ventas desde el último cierre de caja"""
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT 
                    COUNT(*) as cantidad_ventas,
                    COALESCE(SUM(total), 0) as total_vendido,
                    COALESCE(AVG(total), 0) as ticket_promedio,
                    COALESCE(SUM(efectivo), 0) as total_efectivo,
                    COALESCE(SUM(tarjeta), 0) as total_tarjeta,
                    COALESCE(SUM(otro_pago), 0) as total_otros,
                    COALESCE(SUM(valor_igv), 0) as total_igv
                FROM comprobantes 
                WHERE usuario_id = %s 
                AND fecha_emision >= %s
                AND estado = 'emitido'
            """, (usuario_id, fecha_desde))
            return cur.fetchone()

    def crear_cierre_caja(self, tipo_cierre, fecha_inicio, fecha_fin, total_ventas, 
                         ticket_promedio, total_efectivo, total_tarjeta, total_otros_pagos,
                         total_igv, usuario_id, observaciones=""):
        """Crea un nuevo cierre de caja"""
        with self.conn.cursor() as cur:
            cur.execute("""
                INSERT INTO cierre_caja (
                    tipo_cierre, fecha_inicio, fecha_fin, total_ventas, ticket_promedio,
                    total_efectivo, total_tarjeta, total_otros_pagos, total_igv,
                    id_usuario, observaciones
                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """, (tipo_cierre, fecha_inicio, fecha_fin, total_ventas, ticket_promedio,
                  total_efectivo, total_tarjeta, total_otros_pagos, total_igv,
                  usuario_id, observaciones))
            self.conn.commit()

    def get_cierres_caja_por_periodo(self, fecha_inicio, fecha_fin, usuario_id=None):
        """Obtiene los cierres de caja en un período específico"""
        with self.conn.cursor() as cur:
            query = """
                SELECT c.id, c.tipo_cierre, c.fecha_inicio, c.fecha_fin, c.total_ventas,
                       c.ticket_promedio, c.total_efectivo, c.total_tarjeta, c.total_otros_pagos,
                       c.total_igv, c.observaciones, u.nombre as cajero
                FROM cierre_caja c
                JOIN usuarios u ON c.id_usuario = u.id
                WHERE c.fecha_fin BETWEEN %s AND %s
            """
            params = [fecha_inicio, fecha_fin]
            
            if usuario_id:
                query += " AND c.id_usuario = %s"
                params.append(usuario_id)
            
            query += " ORDER BY c.fecha_fin DESC"
            
            cur.execute(query, params)
            return cur.fetchall()

    def get_resumen_caja_actual(self, usuario_id):
        """Obtiene el resumen de la caja actual (desde el último cierre)"""
        with self.conn.cursor() as cur:
            # Obtener el último cierre
            ultimo_cierre = self.get_ultimo_cierre_caja(usuario_id)
            
            if ultimo_cierre:
                fecha_desde = ultimo_cierre[2]  # fecha_fin del último cierre
            else:
                # Si no hay cierres previos, desde el inicio del día
                from datetime import datetime, date
                fecha_desde = datetime.combine(date.today(), datetime.min.time())
            
            # Obtener ventas desde el último cierre
            ventas = self.get_ventas_desde_ultimo_cierre(usuario_id, fecha_desde)
            
            return {
                'ultimo_cierre': ultimo_cierre,
                'ventas_desde_ultimo_cierre': ventas,
                'fecha_desde': fecha_desde
            }

    def actualizar_precios_producto(self, producto_id, nuevos_precios):
        with self.conn.cursor() as cur:
            cur.execute("""
                UPDATE productos
                SET costo_promedio = %s,
                    costo_compra = %s,
                    valor_venta = %s,
                    precio_venta = %s,
                    valor_igv = %s,
                    afecto_igv = %s,
                    fecha_actualizacion = CURRENT_TIMESTAMP,
                    usuario_actualizacion = %s
                WHERE id = %s
            """, (
                nuevos_precios['costo_promedio'],
                nuevos_precios['costo_nuevo'],
                nuevos_precios['valor_venta'],
                nuevos_precios['precio_venta'],
                nuevos_precios.get('valor_igv', 0.0),
                nuevos_precios.get('afecto_igv', True),
                nuevos_precios['usuario_actualizacion'],
                producto_id
            ))
            self.conn.commit()

    
    def insertar_producto(self, datos_producto):
        try:
             with self.conn.cursor() as cur:
                cur.execute("""
                    INSERT INTO productos (
                        descripcion_larga, descripcion_corta, sku, nombre, codigo_barra,
                        plu, costo_compra, costo_promedio, costo_reposicion, precio_regular,
                        valor_venta, precio_venta, activo_promocion, precio_promocion,
                        descuento_porcentaje, afecto_igv, valor_igv, stock_contable,
                        stock_disponible, stock_minimo, venta_promedio, pesable,
                        unidad_medida, marca, registro_sanitario, dias_retiro,
                        case_pack, tipo_producto, categoria, clase, clase_id,
                        referencia, campo1, campo2, campo3, estado, tipo_estado,
                        usuario_actualizacion
                    ) VALUES (%s, %s, %s, %s, %s,
                              %s, %s, %s, %s, %s,
                              %s, %s, %s, %s, %s,
                              %s, %s, %s, %s, %s,
                              %s, %s, %s, %s, %s,
                              %s, %s, %s, %s, %s,
                              %s, %s, %s, %s, %s,
                              %s, %s, %s)
                    RETURNING id
                """, datos_producto)
                producto_id = cur.fetchone()[0]
                self.conn.commit()
                return producto_id
        except Exception as e:
            self.conn.rollback()
            raise Exception(f"[insertar_producto] Error al insertar producto: {e}")

    def insertar_producto_precio(self, producto_id, descripcion_larga, costo_nuevo, costo_promedio, valor_venta_nuevo, precio_venta_nuevo, usuario):
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    INSERT INTO producto_precio (
                        producto_id, descripcion_larga,
                        costo_nuevo, costo_promedio,
                        valor_venta_nuevo, precio_venta_nuevo,
                        usuario_actualizacion
                    ) VALUES (%s, %s, %s, %s, %s, %s, %s)
                """, (producto_id, descripcion_larga, costo_nuevo, costo_promedio, valor_venta_nuevo, precio_venta_nuevo, usuario))
                self.conn.commit()
        except Exception as e:
            self.conn.rollback()
            raise Exception(f"[insertar_producto_precio] Error al insertar precio: {e}")

    # --- BÚSQUEDAS PARA VENTA ---
    def buscar_producto_para_venta_por_barra(self, barra):
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT id, sku, descripcion_larga, unidad_medida, afecto_igv,
                       valor_venta, precio_venta, stock_disponible
                FROM productos
                WHERE codigo_barra = %s
                LIMIT 1
            """, (barra,))
            r = cur.fetchone()
            if not r:
                return None
            return {
                "id": r[0], "sku": r[1], "descripcion_larga": r[2],
                "unidad_medida": r[3], "afecto_igv": r[4],
                "valor_venta": float(r[5] or 0), "precio_venta": float(r[6] or 0),
                "stock_disponible": float(r[7] or 0)
            }

    def buscar_producto_para_venta_por_sku(self, sku):
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT id, sku, descripcion_larga, unidad_medida, afecto_igv,
                       valor_venta, precio_venta, stock_disponible
                FROM productos
                WHERE sku = %s
                LIMIT 1
            """, (sku,))
            r = cur.fetchone()
            if not r:
                return None
            return {
                "id": r[0], "sku": r[1], "descripcion_larga": r[2],
                "unidad_medida": r[3], "afecto_igv": r[4],
                "valor_venta": float(r[5] or 0), "precio_venta": float(r[6] or 0),
                "stock_disponible": float(r[7] or 0)
            }

    def buscar_producto_para_venta_por_descripcion(self, desc):
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT id, sku, descripcion_larga, unidad_medida, afecto_igv,
                       valor_venta, precio_venta, stock_disponible
                FROM productos
                WHERE descripcion_larga ILIKE %s
                ORDER BY descripcion_larga
                LIMIT 10
            """, (f"%{desc}%",))
            rows = cur.fetchall()
            return [{
                "id": r[0], "sku": r[1], "descripcion_larga": r[2],
                "unidad_medida": r[3], "afecto_igv": r[4],
                "valor_venta": float(r[5] or 0), "precio_venta": float(r[6] or 0),
                "stock_disponible": float(r[7] or 0)
            } for r in rows]

    # --- OPERACIONES DE VENTA ---
    def crear_venta(self, usuario, sesion_id, subtotal, igv, total, tipo_pago):
        with self.conn.cursor() as cur:
            cur.execute("""
                INSERT INTO ventas (usuario, sesion_id, subtotal, igv, total, tipo_pago, estado, fecha)
                VALUES (%s, %s, %s, %s, %s, %s, 'emitida', NOW())
                RETURNING id
            """, (usuario, sesion_id, subtotal, igv, total, tipo_pago))
            vid = cur.fetchone()[0]
            self.conn.commit()
            return vid

    def agregar_detalle_venta(self, venta_id, producto_id, cantidad, valor_unitario, igv_unitario, precio_unitario, total_linea):
        with self.conn.cursor() as cur:
            cur.execute("""
                INSERT INTO venta_detalle
                    (venta_id, producto_id, cantidad, valor_unitario, igv_unitario, precio_unitario, total_linea)
                VALUES (%s, %s, %s, %s, %s, %s, %s)
            """, (venta_id, producto_id, cantidad, valor_unitario, igv_unitario, precio_unitario, total_linea))
            self.conn.commit()

    def descontar_stock_producto(self, producto_id, cantidad):
        with self.conn.cursor() as cur:
            cur.execute("""
                UPDATE productos
                SET stock_contable = stock_contable - %s,
                    stock_disponible = stock_disponible - %s,
                    fecha_actualizacion = NOW()
                WHERE id = %s
            """, (cantidad, cantidad, producto_id))
            self.conn.commit()

    #--- FIN VENTAS ###---

    # ========== COMPROBANTES ==========
    def crear_comprobante(self, *, tipo:str, serie:str, numero:str, cliente_id:int|None,
                          dni:str|None, usuario_id:int, medio_pago:str,
                          efectivo:float, tarjeta:float, otro_pago:float,
                          terminal_id:int, observaciones:str|None):
        with self.conn.cursor() as cur:
            cur.execute("""
                INSERT INTO comprobantes(
                    tipo, serie, numero, cliente_id, dni, usuario_id, medio_pago,
                    efectivo, tarjeta, otro_pago, observaciones, terminal_id, estado
                ) VALUES (%s,%s,%s,%s,%s,%s,%s, %s,%s,%s, %s, %s, 'pendiente')
                RETURNING id
            """, (tipo, serie, numero, cliente_id, dni, usuario_id, medio_pago,
                  efectivo, tarjeta, otro_pago, observaciones, terminal_id))
            cid = cur.fetchone()[0]
            self.conn.commit()
            return cid

    def agregar_detalle_comprobante(self, *, comprobante_id:int, producto_id:int, descripcion:str,
                                    cantidad:float, precio_unitario:float,
                                    afecto_igv:bool):
        """
        precio_unitario = precio con IGV si afecto_igv=True; si NO afecto, precio=valor.
        """
        igv = round((precio_unitario/1.18)*0.18,2) if afecto_igv else 0.0
        valor = round(precio_unitario - igv, 2) if afecto_igv else precio_unitario
        tipo_afect = 'GRAVADO' if afecto_igv else 'INAFECTO'
        subtotal = round(valor * cantidad, 2)
        igv_total = round(igv * cantidad, 2)

        with self.conn.cursor() as cur:
            cur.execute("""
                INSERT INTO detalle_comprobante(
                    comprobante_id, producto_id, descripcion_producto,
                    cantidad, precio_unitario, igv, subtotal, descto, tipo_afectacion, estado
                ) VALUES (%s,%s,%s,%s,%s,%s,%s,0,%s,'Activo')
            """, (comprobante_id, producto_id, descripcion, cantidad,
                  precio_unitario, igv_total, subtotal, tipo_afect))
            self.conn.commit()

    def recalcular_totales(self, comprobante_id:int):
        with self.conn.cursor() as cur:
            cur.execute("SELECT f_recalcular_totales_comprobante(%s)", (comprobante_id,))
            self.conn.commit()

    def finalizar_comprobante(self, comprobante_id:int):
        with self.conn.cursor() as cur:
            cur.execute("SELECT f_finalizar_comprobante(%s)", (comprobante_id,))
            self.conn.commit()

    # ========== BÚSQUEDAS RÁPIDAS PARA VENTA ==========
    def buscar_producto_para_venta_por_barra(self, barra:str):
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT id, sku, descripcion_larga, unidad_medida, afecto_igv,
                       valor_venta, precio_venta, stock_disponible
                FROM productos
                WHERE codigo_barra = %s
                LIMIT 1
            """, (barra,))
            r = cur.fetchone()
            if not r: return None
            return {
                "id": r[0], "sku": r[1], "descripcion_larga": r[2],
                "unidad_medida": r[3], "afecto_igv": r[4],
                "valor_venta": float(r[5] or 0), "precio_venta": float(r[6] or 0),
                "stock_disponible": float(r[7] or 0)
            }

    def buscar_producto_para_venta_por_sku(self, sku:str):
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT id, sku, descripcion_larga, unidad_medida, afecto_igv,
                       valor_venta, precio_venta, stock_disponible
                FROM productos
                WHERE sku = %s
                LIMIT 1
            """, (sku,))
            r = cur.fetchone()
            if not r: return None
            return {
                "id": r[0], "sku": r[1], "descripcion_larga": r[2],
                "unidad_medida": r[3], "afecto_igv": r[4],
                "valor_venta": float(r[5] or 0), "precio_venta": float(r[6] or 0),
                "stock_disponible": float(r[7] or 0)
            }

    def buscar_producto_para_venta_por_descripcion(self, desc:str):
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT id, sku, descripcion_larga, unidad_medida, afecto_igv,
                       valor_venta, precio_venta, stock_disponible
                FROM productos
                WHERE descripcion_larga ILIKE %s
                ORDER BY descripcion_larga
                LIMIT 15
            """, (f"%{desc}%",))
            rs = cur.fetchall()
            return [{
                "id": r[0], "sku": r[1], "descripcion_larga": r[2],
                "unidad_medida": r[3], "afecto_igv": r[4],
                "valor_venta": float(r[5] or 0), "precio_venta": float(r[6] or 0),
                "stock_disponible": float(r[7] or 0)
            } for r in rs]

          
    def registra_sesion(self, usuario: str, terminal: str, local: str) -> int:
        """Registra el inicio de sesión y retorna el ID de la sesión"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    INSERT INTO sesiones 
                    (usuario, terminal, local, tipo_conexion, fecha_registro)
                    VALUES (%s, %s, %s, 'login', NOW())
                    RETURNING id
                """, (usuario, terminal, local))
                sesion_id = cur.fetchone()[0]
                self.conn.commit()
                print(f"Sesión ID al iniciar: {sesion_id} - Tipo: {type(sesion_id)}")
                return sesion_id
        except Exception as e:
            self.conn.rollback()
            raise Exception(f"Error al iniciar sesión: {e}")

    # db_client.py (fragmentos nuevos/relevantes)

    def get_terminal_nombre_desde_config(self, cfg: dict) -> str:
        """
        De tu config.json: {"terminal": "06", ...}
        Usaremos ese valor como terminal.nombre.
        """
        return (cfg or {}).get("terminal", "").strip()



    def datos_terminal(self, terminal_nombre: str, tipo: str):
        """Opcional: para conocer serie/número actual sin avanzar."""
        with self.conn.cursor() as cur:
            cur.execute("""
                SELECT id_terminal, serie, numero, local, serie_impresora, modo_impresora, config_puerto
                FROM terminal
                WHERE nombre=%s AND tipo_comprobante=%s AND activo=true
            """, (terminal_nombre, tipo))
            r = cur.fetchone()
            if not r: return None
            return {
                "id_terminal": r[0], "serie": r[1], "numero": r[2],
                "local": r[3], "serie_impresora": r[4],
                "modo_impresora": r[5], "config_puerto": r[6]
            }


    def cerrar_sesion(self, sesion_id: int):
        """Versión usando QTime en lugar de NOW()"""
        try:
            with self.conn.cursor() as cur:
                # 1. Verificar sesión activa
                cur.execute("""
                    SELECT id, fecha_registro 
                    FROM sesiones 
                    WHERE id = %s AND tipo_conexion = 'login'
                    FOR UPDATE
                """, (sesion_id,))
                
                sesion = cur.fetchone()
                if not sesion:
                    raise ValueError(f"No existe sesión activa con ID {sesion_id}")

                # 2. Obtener fecha/hora actual desde Qt
                fecha_real = QDateTime.currentDateTime()
                hora_real = QTime.currentTime()
                
                # Convertir a formato compatible con PostgreSQL
                fecha_logout_str = fecha_real.toString("yyyy-MM-dd HH:mm:ss")
                #print(f"[DEBUG] Hora Qt actual: {hora_real.toString('HH:mm:ss.zzz')}")
                #print(f"[DEBUG] Fecha Qt completa: {fecha_logout_str}")

                # 3. Calcular diferencia usando la fecha_registro de PostgreSQL
                # y la fecha actual de Qt
                cur.execute("""
                    UPDATE sesiones 
                    SET 
                        tipo_conexion = 'logout',
                        tiempo_conexion = ROUND(
                            EXTRACT(EPOCH FROM (TIMESTAMP %s - fecha_registro))/60, 
                            2
                        ),
                        fecha_logout = %s
                    WHERE id = %s
                    RETURNING fecha_logout, tiempo_conexion
                """, (fecha_logout_str, fecha_logout_str, sesion_id))
                
                # 4. Verificar resultados
                resultado = cur.fetchone()
                #print(f"[DEBUG] Fecha logout registrada en BD: {resultado[0]}")
                #print(f"[DEBUG] Tiempo conexión calculado: {resultado[1]} minutos")

                self.conn.commit()
                return True
                
        except Exception as e:
            self.conn.rollback()
            print(f"[ERROR] Detalles del error: {str(e)}")
            raise Exception(f"Error al cerrar sesión: {str(e)}")

    # === FUNCIONES PARA LA TABLA VENTA ===
    
    def insertar_venta(self, comprobante_id: int, producto_id: int, nombre_producto: str,
                       cantidad: float, precio_unitario: float, igv: float, subtotal: float,
                       descto: float, total: float, cliente: str, dni: str, medio_pago: str,
                       terminal_id: int, usuario_id: int) -> int:
        """
        Inserta un registro en la tabla venta con campos calculados de fecha
        """
        try:
            from datetime import datetime
            
            fecha_actual = datetime.now()
            anio = fecha_actual.year
            mes = fecha_actual.month
            mes_anio = int(f"{anio:04d}{mes:02d}")
            dia = fecha_actual.day
            semana = fecha_actual.isocalendar()[1]  # Semana del año
            
            with self.conn.cursor() as cur:
                cur.execute("""
                    INSERT INTO venta (
                        comprobante_id, producto_id, nombre_producto, cantidad, precio_unitario,
                        igv, subtotal, descto, total, fecha_venta, cliente, dni, medio_pago,
                        terminal_id, usuario_id, anio, mes, mes_anio, dia, semana
                    ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                    RETURNING id
                """, (comprobante_id, producto_id, nombre_producto, cantidad, precio_unitario,
                      igv, subtotal, descto, total, fecha_actual, cliente, dni, medio_pago,
                      terminal_id, usuario_id, anio, mes, mes_anio, dia, semana))
                
                venta_id = cur.fetchone()[0]
                self.conn.commit()
                return venta_id
                
        except Exception as e:
            self.conn.rollback()
            raise Exception(f"Error al insertar venta: {e}")
    
    def obtener_ventas_por_comprobante(self, comprobante_id: int) -> list:
        """
        Obtiene todas las ventas asociadas a un comprobante
        """
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT * FROM venta WHERE comprobante_id = %s ORDER BY id
                """, (comprobante_id,))
                return cur.fetchall()
        except Exception as e:
            raise Exception(f"Error al obtener ventas del comprobante: {e}")
    
    def obtener_ventas_por_fecha(self, fecha_inicio: str, fecha_fin: str) -> list:
        """
        Obtiene ventas en un rango de fechas
        fecha_inicio y fecha_fin en formato 'YYYY-MM-DD'
        """
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT * FROM venta 
                    WHERE fecha_venta::date BETWEEN %s AND %s 
                    ORDER BY fecha_venta DESC
                """, (fecha_inicio, fecha_fin))
                return cur.fetchall()
        except Exception as e:
            raise Exception(f"Error al obtener ventas por fecha: {e}")
    
    def obtener_resumen_ventas_mes(self, mes_anio: int) -> dict:
        """
        Obtiene resumen de ventas para un mes específico
        mes_anio en formato YYYYMM (ejemplo: 202501 para enero 2025)
        """
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        COUNT(*) as total_ventas,
                        SUM(total) as monto_total,
                        SUM(cantidad) as unidades_vendidas,
                        COUNT(DISTINCT comprobante_id) as comprobantes,
                        COUNT(DISTINCT usuario_id) as usuarios
                    FROM venta 
                    WHERE mes_anio = %s
                """, (mes_anio,))
                
                resultado = cur.fetchone()
                if resultado:
                    return {
                        "total_ventas": resultado[0] or 0,
                        "monto_total": float(resultado[1] or 0),
                        "unidades_vendidas": float(resultado[2] or 0),
                        "comprobantes": resultado[3] or 0,
                        "usuarios": resultado[4] or 0
                    }
                return {"total_ventas": 0, "monto_total": 0, "unidades_vendidas": 0, "comprobantes": 0, "usuarios": 0}
                
        except Exception as e:
            raise Exception(f"Error al obtener resumen de ventas: {e}")

    # ==================== FUNCIONES PARA DASHBOARD ====================
    
    def get_ventas_del_dia(self, fecha):
        """Obtener ventas del día específico"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT v.id, v.total, v.fecha_venta, v.usuario_id
                    FROM venta v
                    WHERE DATE(v.fecha_venta) = %s
                    ORDER BY v.fecha_venta DESC
                """, (fecha,))
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo ventas del día: {e}")
            self.conn.rollback()
            return []
    
    def get_ventas_por_hora(self, fecha):
        """Obtener ventas agrupadas por hora para el día"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        EXTRACT(HOUR FROM fecha_venta) as hora,
                        COUNT(*) as cantidad_ventas,
                        SUM(total) as total_ventas
                    FROM venta 
                    WHERE DATE(fecha_venta) = %s
                    GROUP BY EXTRACT(HOUR FROM fecha_venta)
                    ORDER BY hora
                """, (fecha,))
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo ventas por hora: {e}")
            self.conn.rollback()
            return []
    
    def get_top_productos_dia(self, fecha, limit=5):
        """Obtener top productos vendidos en el día"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        p.descripcion_corta,
                        SUM(v.cantidad) as cantidad_vendida,
                        SUM(v.total) as total_vendido
                    FROM venta v
                    JOIN productos p ON v.producto_id = p.id
                    WHERE DATE(v.fecha_venta) = %s
                    GROUP BY p.id, p.descripcion_corta
                    ORDER BY cantidad_vendida DESC
                    LIMIT %s
                """, (fecha, limit))
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo top productos: {e}")
            self.conn.rollback()
            return []
    
    def get_productos_stock_bajo(self):
        """Obtener productos con stock bajo"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        descripcion_corta,
                        stock_disponible,
                        stock_minimo
                    FROM productos 
                    WHERE stock_disponible <= stock_minimo
                    ORDER BY (stock_disponible - stock_minimo) ASC
                """)
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo productos con stock bajo: {e}")
            self.conn.rollback()
            return []
    
    def get_estado_caja_actual(self):
        """Obtener estado actual de la caja"""
        try:
            with self.conn.cursor() as cur:
                # Obtener ventas del día actual
                cur.execute("""
                    SELECT 
                        SUM(CASE WHEN medio_pago = 'Efectivo' THEN total ELSE 0 END) as efectivo,
                        SUM(CASE WHEN medio_pago = 'Tarjeta' THEN total ELSE 0 END) as tarjeta,
                        SUM(CASE WHEN medio_pago NOT IN ('Efectivo', 'Tarjeta') THEN total ELSE 0 END) as otros
                    FROM venta 
                    WHERE DATE(fecha_venta) = CURRENT_DATE
                """)
                
                ventas_dia = cur.fetchone()
                if ventas_dia:
                    return {
                        'efectivo': float(ventas_dia[0] or 0),
                        'tarjeta': float(ventas_dia[1] or 0),
                        'otros': float(ventas_dia[2] or 0),
                        'total': float((ventas_dia[0] or 0) + (ventas_dia[1] or 0) + (ventas_dia[2] or 0))
                    }
                
                return {'efectivo': 0, 'tarjeta': 0, 'otros': 0, 'total': 0}
        except Exception as e:
            print(f"Error obteniendo estado de caja: {e}")
            self.conn.rollback()
            return {'efectivo': 0, 'tarjeta': 0, 'otros': 0, 'total': 0}
    
    def get_estadisticas_ventas_periodo(self, fecha_inicio, fecha_fin):
        """Obtener estadísticas de ventas para un período"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        COUNT(*) as total_ventas,
                        SUM(total) as monto_total,
                        AVG(total) as promedio_venta,
                        COUNT(DISTINCT usuario_id) as usuarios_activos,
                        COUNT(DISTINCT producto_id) as productos_vendidos
                    FROM venta 
                    WHERE DATE(fecha_venta) BETWEEN %s AND %s
                """, (fecha_inicio, fecha_fin))
                
                resultado = cur.fetchone()
                if resultado:
                    return {
                        'total_ventas': resultado[0] or 0,
                        'monto_total': float(resultado[1] or 0),
                        'promedio_venta': float(resultado[2] or 0),
                        'usuarios_activos': resultado[3] or 0,
                        'productos_vendidos': resultado[4] or 0
                    }
                return {'total_ventas': 0, 'monto_total': 0, 'promedio_venta': 0, 'usuarios_activos': 0, 'productos_vendidos': 0}
        except Exception as e:
            print(f"Error obteniendo estadísticas de ventas: {e}")
            self.conn.rollback()
            return {'total_ventas': 0, 'monto_total': 0, 'promedio_venta': 0, 'usuarios_activos': 0, 'productos_vendidos': 0}

    # ==================== GESTIÓN DE STOCK AVANZADA ====================
    
    def get_productos_con_stock(self):
        """Obtener todos los productos con información de stock"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        id, descripcion_corta, sku, nombre, codigo_barra,
                        stock_contable, stock_disponible, stock_minimo,
                        precio_venta, costo_promedio, tipo_estado, descripcion_larga
                    FROM productos 
                    WHERE tipo_estado = 'activo'
                    ORDER BY descripcion_corta
                """)
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo productos con stock: {e}")
            self.conn.rollback()
            return []
    
    def actualizar_stock_producto(self, producto_id, nuevo_stock, tipo_movimiento, cantidad, motivo, usuario_id, documento_referencia=None):
        """Actualizar stock de un producto y registrar el movimiento"""
        try:
            with self.conn.cursor() as cur:
                # Obtener stock actual
                cur.execute("SELECT stock_disponible FROM productos WHERE id = %s", (producto_id,))
                stock_actual = cur.fetchone()
                
                if not stock_actual:
                    return False, "Producto no encontrado"
                
                stock_anterior = stock_actual[0] or 0
                
                # Calcular nuevo stock según el tipo de movimiento
                if tipo_movimiento == 'entrada':
                    stock_final = stock_anterior + cantidad
                elif tipo_movimiento == 'salida':
                    if stock_anterior < cantidad:
                        return False, "Stock insuficiente"
                    stock_final = stock_anterior - cantidad
                elif tipo_movimiento == 'ajuste':
                    stock_final = nuevo_stock
                    cantidad = abs(nuevo_stock - stock_anterior)
                else:
                    return False, "Tipo de movimiento inválido"
                
                # Actualizar stock en la tabla productos
                cur.execute("""
                    UPDATE productos 
                    SET stock_disponible = %s, stock_contable = %s
                    WHERE id = %s
                """, (stock_final, stock_final, producto_id))
                
                # Registrar movimiento en la tabla de movimientos (si existe)
                try:
                    cur.execute("""
                        INSERT INTO stock_movements 
                        (product_id, movement_type, quantity, user_id, reason, reference_document)
                        VALUES (%s, %s, %s, %s, %s, %s)
                    """, (producto_id, tipo_movimiento, cantidad, usuario_id, motivo, documento_referencia))
                except:
                    # Si la tabla no existe, continuar sin error
                    pass
                
                self.conn.commit()
                return True, f"Stock actualizado: {stock_anterior} → {stock_final}"
                
        except Exception as e:
            print(f"Error actualizando stock: {e}")
            self.conn.rollback()
            return False, f"Error: {str(e)}"
    
    def get_movimientos_stock(self, producto_id=None, fecha_inicio=None, fecha_fin=None, limit=100):
        """Obtener movimientos de stock (simulado hasta crear la tabla)"""
        try:
            # Por ahora retornar lista vacía hasta que se cree la tabla stock_movements
            return []
        except Exception as e:
            print(f"Error obteniendo movimientos de stock: {e}")
            self.conn.rollback()
            return []
    
    def get_productos_stock_bajo_avanzado(self, limite_stock=None):
        """Obtener productos con stock bajo (avanzado)"""
        try:
            with self.conn.cursor() as cur:
                if limite_stock is None:
                    # Usar stock mínimo del producto
                    cur.execute("""
                        SELECT 
                            id, descripcion_corta, sku, stock_disponible, stock_minimo,
                            precio_venta, costo_promedio,
                            (stock_disponible - stock_minimo) as diferencia
                        FROM productos 
                        WHERE tipo_estado = 'activo' AND stock_disponible <= stock_minimo
                        ORDER BY diferencia ASC
                    """)
                else:
                    # Usar límite personalizado
                    cur.execute("""
                        SELECT 
                            id, descripcion_corta, sku, stock_disponible, stock_minimo,
                            precio_venta, costo_promedio,
                            (stock_disponible - %s) as diferencia
                        FROM productos 
                        WHERE tipo_estado = 'activo' AND stock_disponible <= %s
                        ORDER BY diferencia ASC
                    """, (limite_stock, limite_stock))
                
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo productos con stock bajo: {e}")
            self.conn.rollback()
            return []
    
    def get_estadisticas_stock(self):
        """Obtener estadísticas generales de stock"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        COUNT(*) as total_productos,
                        SUM(stock_disponible) as stock_total,
                        SUM(stock_disponible * costo_promedio) as valor_inventario,
                        COUNT(CASE WHEN stock_disponible <= stock_minimo THEN 1 END) as productos_stock_bajo,
                        COUNT(CASE WHEN stock_disponible = 0 THEN 1 END) as productos_sin_stock,
                        AVG(stock_disponible) as promedio_stock
                    FROM productos
                    WHERE tipo_estado = 'activo'
                """)
                
                resultado = cur.fetchone()
                if resultado:
                    return {
                        'total_productos': resultado[0] or 0,
                        'stock_total': resultado[1] or 0,
                        'valor_inventario': float(resultado[2] or 0),
                        'productos_stock_bajo': resultado[3] or 0,
                        'productos_sin_stock': resultado[4] or 0,
                        'promedio_stock': float(resultado[5] or 0)
                    }
                return {'total_productos': 0, 'stock_total': 0, 'valor_inventario': 0, 
                       'productos_stock_bajo': 0, 'productos_sin_stock': 0, 'promedio_stock': 0}
        except Exception as e:
            print(f"Error obteniendo estadísticas de stock: {e}")
            self.conn.rollback()
            return {'total_productos': 0, 'stock_total': 0, 'valor_inventario': 0, 
                   'productos_stock_bajo': 0, 'productos_sin_stock': 0, 'promedio_stock': 0}
    
    # ==================== REPORTES DE VENTAS ====================
    
    def get_ventas_por_periodo(self, fecha_desde, fecha_hasta, tipo_periodo='diario'):
        """Obtener ventas agrupadas por período"""
        try:
            cur = self.conn.cursor()
            try:
                # Normalizar tipo_periodo: 'personalizado' se trata como 'diario'
                if tipo_periodo == 'personalizado':
                    tipo_periodo = 'diario'
                
                if tipo_periodo == 'diario':
                    cur.execute("""
                        SELECT 
                            DATE(fecha_venta) as periodo,
                            COALESCE(SUM(total), 0) as ventas_totales,
                            COUNT(DISTINCT comprobante_id) as num_tickets,
                            COALESCE(AVG(total), 0) as promedio_ticket
                        FROM venta 
                        WHERE DATE(fecha_venta) BETWEEN %s AND %s
                        GROUP BY DATE(fecha_venta)
                        ORDER BY periodo
                    """, (fecha_desde, fecha_hasta))
                elif tipo_periodo == 'semanal':
                    cur.execute("""
                        SELECT 
                            EXTRACT(YEAR FROM fecha_venta)::INTEGER as año,
                            EXTRACT(WEEK FROM fecha_venta)::INTEGER as semana,
                            COALESCE(SUM(total), 0) as ventas_totales,
                            COUNT(DISTINCT comprobante_id) as num_tickets,
                            COALESCE(AVG(total), 0) as promedio_ticket
                        FROM venta 
                        WHERE DATE(fecha_venta) BETWEEN %s AND %s
                        GROUP BY EXTRACT(YEAR FROM fecha_venta), EXTRACT(WEEK FROM fecha_venta)
                        ORDER BY año, semana
                    """, (fecha_desde, fecha_hasta))
                elif tipo_periodo == 'mensual':
                    cur.execute("""
                        SELECT 
                            EXTRACT(YEAR FROM fecha_venta)::INTEGER as año,
                            EXTRACT(MONTH FROM fecha_venta)::INTEGER as mes,
                            COALESCE(SUM(total), 0) as ventas_totales,
                            COUNT(DISTINCT comprobante_id) as num_tickets,
                            COALESCE(AVG(total), 0) as promedio_ticket
                        FROM venta 
                        WHERE DATE(fecha_venta) BETWEEN %s AND %s
                        GROUP BY EXTRACT(YEAR FROM fecha_venta), EXTRACT(MONTH FROM fecha_venta)
                        ORDER BY año, mes
                    """, (fecha_desde, fecha_hasta))
                elif tipo_periodo == 'anual':
                    cur.execute("""
                        SELECT 
                            EXTRACT(YEAR FROM fecha_venta)::INTEGER as año,
                            COALESCE(SUM(total), 0) as ventas_totales,
                            COUNT(DISTINCT comprobante_id) as num_tickets,
                            COALESCE(AVG(total), 0) as promedio_ticket
                        FROM venta 
                        WHERE DATE(fecha_venta) BETWEEN %s AND %s
                        GROUP BY EXTRACT(YEAR FROM fecha_venta)
                        ORDER BY año
                    """, (fecha_desde, fecha_hasta))
                else:
                    # Si no se reconoce el tipo, usar diario por defecto
                    cur.execute("""
                        SELECT 
                            DATE(fecha_venta) as periodo,
                            COALESCE(SUM(total), 0) as ventas_totales,
                            COUNT(DISTINCT comprobante_id) as num_tickets,
                            COALESCE(AVG(total), 0) as promedio_ticket
                        FROM venta 
                        WHERE DATE(fecha_venta) BETWEEN %s AND %s
                        GROUP BY DATE(fecha_venta)
                        ORDER BY periodo
                    """, (fecha_desde, fecha_hasta))
                
                resultados = cur.fetchall()
                cur.close()
                return resultados if resultados else []
            except Exception as e:
                cur.close()
                raise e
        except Exception as e:
            print(f"Error obteniendo ventas por período: {e}")
            self.conn.rollback()
            return []
    
    def get_ventas_por_jerarquia(self, fecha_desde, fecha_hasta, departamento_id=None, subdepartamento_id=None, clase_id=None):
        """Obtener ventas por jerarquía de productos"""
        try:
            cur = self.conn.cursor()
            try:
                where_conditions = ["DATE(v.fecha_venta) BETWEEN %s AND %s"]
                params = [fecha_desde, fecha_hasta]
                
                if clase_id:
                    where_conditions.append("p.clase_id = %s")
                    params.append(clase_id)
                
                cur.execute(f"""
                    SELECT 
                        COALESCE(d.nombre, 'Sin Departamento') as departamento,
                        COALESCE(sd.nombre, 'Sin Subdepartamento') as subdepartamento,
                        COALESCE(c.nombre, 'Sin Clase') as clase,
                        COALESCE(SUM(v.total), 0) as ventas_totales,
                        COUNT(DISTINCT v.comprobante_id) as num_ventas,
                        COUNT(DISTINCT p.id) as num_productos
                    FROM venta v
                    JOIN productos p ON v.producto_id = p.id
                    LEFT JOIN clase c ON p.clase_id = c.id
                    LEFT JOIN subdepartamento sd ON c.subdepartamento_id = sd.id
                    LEFT JOIN departamento d ON sd.departamento_id = d.id
                    WHERE {' AND '.join(where_conditions)}
                    GROUP BY d.nombre, sd.nombre, c.nombre
                    ORDER BY ventas_totales DESC
                """, params)
                
                resultados = cur.fetchall()
                cur.close()
                return resultados if resultados else []
            except Exception as e:
                cur.close()
                raise e
        except Exception as e:
            print(f"Error obteniendo ventas por jerarquía: {e}")
            self.conn.rollback()
            return []
    
    def get_ventas_por_marca(self, fecha_desde, fecha_hasta, top_n=10):
        """Obtener ventas por marca"""
        try:
            cur = self.conn.cursor()
            try:
                cur.execute("""
                    SELECT 
                        COALESCE(p.marca, 'Sin Marca') as marca,
                        COALESCE(SUM(v.total), 0) as ventas_totales,
                        COUNT(DISTINCT v.comprobante_id) as num_ventas,
                        COUNT(DISTINCT p.id) as num_productos,
                        ROUND((COALESCE(SUM(v.total), 0) * 100.0 / NULLIF((SELECT SUM(total) FROM venta WHERE DATE(fecha_venta) BETWEEN %s AND %s), 0)), 2) as participacion_porcentaje
                    FROM venta v
                    JOIN productos p ON v.producto_id = p.id
                    WHERE DATE(v.fecha_venta) BETWEEN %s AND %s
                    GROUP BY p.marca
                    ORDER BY ventas_totales DESC
                    LIMIT %s
                """, (fecha_desde, fecha_hasta, fecha_desde, fecha_hasta, top_n))
                
                resultados = cur.fetchall()
                cur.close()
                return resultados if resultados else []
            except Exception as e:
                cur.close()
                raise e
        except Exception as e:
            print(f"Error obteniendo ventas por marca: {e}")
            self.conn.rollback()
            return []
    
    def get_comparacion_ventas(self, fecha_desde, fecha_hasta, tipo_comparacion='semanas'):
        """Obtener comparación de ventas entre períodos"""
        try:
            cur = self.conn.cursor()
            try:
                if tipo_comparacion == 'semanas':
                    cur.execute("""
                        SELECT 
                            EXTRACT(WEEK FROM fecha_venta)::INTEGER as semana,
                            COALESCE(SUM(total), 0) as ventas_totales,
                            COUNT(DISTINCT comprobante_id) as num_tickets,
                            COALESCE(AVG(total), 0) as promedio_ticket
                        FROM venta 
                        WHERE DATE(fecha_venta) BETWEEN %s AND %s
                        GROUP BY EXTRACT(WEEK FROM fecha_venta)
                        ORDER BY semana
                    """, (fecha_desde, fecha_hasta))
                elif tipo_comparacion == 'meses':
                    cur.execute("""
                        SELECT 
                            EXTRACT(MONTH FROM fecha_venta)::INTEGER as mes,
                            COALESCE(SUM(total), 0) as ventas_totales,
                            COUNT(DISTINCT comprobante_id) as num_tickets,
                            COALESCE(AVG(total), 0) as promedio_ticket
                        FROM venta 
                        WHERE DATE(fecha_venta) BETWEEN %s AND %s
                        GROUP BY EXTRACT(MONTH FROM fecha_venta)
                        ORDER BY mes
                    """, (fecha_desde, fecha_hasta))
                elif tipo_comparacion == 'años':
                    cur.execute("""
                        SELECT 
                            EXTRACT(YEAR FROM fecha_venta)::INTEGER as año,
                            COALESCE(SUM(total), 0) as ventas_totales,
                            COUNT(DISTINCT comprobante_id) as num_tickets,
                            COALESCE(AVG(total), 0) as promedio_ticket
                        FROM venta 
                        WHERE DATE(fecha_venta) BETWEEN %s AND %s
                        GROUP BY EXTRACT(YEAR FROM fecha_venta)
                        ORDER BY año
                    """, (fecha_desde, fecha_hasta))
                else:
                    cur.close()
                    return []
                
                resultados = cur.fetchall()
                cur.close()
                return resultados if resultados else []
            except Exception as e:
                cur.close()
                raise e
        except Exception as e:
            print(f"Error obteniendo comparación de ventas: {e}")
            self.conn.rollback()
            return []
    
    def get_ventas_avanzado(self, fecha_desde, fecha_hasta, usuario_id=None, cliente_id=None, venta_min=0, venta_max=999999):
        """Obtener análisis avanzado de ventas"""
        try:
            cur = self.conn.cursor()
            try:
                where_conditions = [
                    "DATE(v.fecha_venta) BETWEEN %s AND %s",
                    "v.total BETWEEN %s AND %s"
                ]
                params = [fecha_desde, fecha_hasta, venta_min, venta_max]
                
                if usuario_id:
                    where_conditions.append("v.usuario_id = %s")
                    params.append(usuario_id)
                
                if cliente_id:
                    where_conditions.append("v.cliente_id = %s")
                    params.append(cliente_id)
                
                cur.execute(f"""
                    SELECT 
                        DATE(v.fecha_venta) as fecha,
                        COALESCE(u.nombre, 'Sin Usuario') as usuario,
                        COALESCE(v.cliente, 'Cliente General') as cliente,
                        v.cantidad as num_productos,
                        COALESCE(v.total, 0) as ventas_totales,
                        v.comprobante_id as num_tickets,
                        COALESCE(v.total, 0) as promedio_ticket
                    FROM venta v
                    LEFT JOIN usuarios u ON v.usuario_id = u.id
                    WHERE {' AND '.join(where_conditions)}
                    ORDER BY v.fecha_venta DESC
                """, params)
                
                resultados = cur.fetchall()
                cur.close()
                return resultados if resultados else []
            except Exception as e:
                cur.close()
                raise e
        except Exception as e:
            print(f"Error obteniendo análisis avanzado: {e}")
            self.conn.rollback()
            return []
    
    def get_departamentos_para_reporte(self):
        """Obtener lista de departamentos para reportes"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("SELECT id, nombre FROM departamento ORDER BY nombre")
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo departamentos: {e}")
            self.conn.rollback()
            return []
    
    def get_subdepartamentos_para_reporte(self, departamento_id=None):
        """Obtener lista de subdepartamentos para reportes"""
        try:
            with self.conn.cursor() as cur:
                if departamento_id:
                    cur.execute("SELECT id, nombre FROM subdepartamento WHERE departamento_id = %s ORDER BY nombre", (departamento_id,))
                else:
                    cur.execute("SELECT id, nombre FROM subdepartamento ORDER BY nombre")
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo subdepartamentos: {e}")
            self.conn.rollback()
            return []
    
    def get_clases_para_reporte(self, subdepartamento_id=None):
        """Obtener lista de clases para reportes"""
        try:
            with self.conn.cursor() as cur:
                if subdepartamento_id:
                    cur.execute("SELECT id, nombre FROM clase WHERE subdepartamento_id = %s ORDER BY nombre", (subdepartamento_id,))
                else:
                    cur.execute("SELECT id, nombre FROM clase ORDER BY nombre")
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo clases: {e}")
            self.conn.rollback()
            return []
    
    def get_usuarios_para_reporte(self):
        """Obtener lista de usuarios para reportes"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("SELECT id, nombre FROM usuarios ORDER BY nombre")
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo usuarios: {e}")
            self.conn.rollback()
            return []
    
    def get_clientes_para_reporte(self):
        """Obtener lista de clientes para reportes"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("SELECT id, nombre FROM clientes ORDER BY nombre")
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo clientes: {e}")
            self.conn.rollback()
            return []
    
    # ==================== REPORTES DE PRODUCTOS ====================
    
    def get_productos_mas_vendidos(self, fecha_desde, fecha_hasta, top_n=10, orden_por='cantidad'):
        """Obtener productos más vendidos por período"""
        try:
            with self.conn.cursor() as cur:
                if orden_por == 'cantidad':
                    order_field = "SUM(v.cantidad) DESC"
                else:  # orden_por == 'ingresos'
                    order_field = "SUM(v.total) DESC"
                
                cur.execute(f"""
                    SELECT 
                        p.id,
                        p.descripcion_corta,
                        p.descripcion_larga,
                        p.marca,
                        p.codigo_barra,
                        p.sku,
                        SUM(v.cantidad) as total_cantidad,
                        SUM(v.total) as total_ingresos,
                        COUNT(DISTINCT v.comprobante_id) as num_ventas,
                        AVG(v.precio_unitario) as precio_promedio,
                        p.stock_disponible,
                        p.stock_minimo
                    FROM venta v
                    JOIN productos p ON v.producto_id = p.id
                    WHERE DATE(v.fecha_venta) BETWEEN %s AND %s
                    GROUP BY p.id, p.descripcion_corta, p.descripcion_larga, p.marca, 
                             p.codigo_barra, p.sku, p.stock_disponible, p.stock_minimo
                    ORDER BY {order_field}
                    LIMIT %s
                """, (fecha_desde, fecha_hasta, top_n))
                
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo productos más vendidos: {e}")
            self.conn.rollback()
            return []
    
    def get_productos_stock_bajo_reporte(self, stock_minimo=None):
        """Obtener productos con stock bajo para reportes"""
        try:
            with self.conn.cursor() as cur:
                if stock_minimo is None:
                    # Usar stock mínimo configurado en cada producto
                    cur.execute("""
                        SELECT 
                            p.id,
                            p.descripcion_corta,
                            p.descripcion_larga,
                            p.marca,
                            p.codigo_barra,
                            p.sku,
                            p.stock_disponible,
                            p.stock_minimo,
                            p.precio_venta,
                            p.costo_promedio,
                            CASE 
                                WHEN p.stock_disponible <= 0 THEN 'Sin Stock'
                                WHEN p.stock_disponible <= p.stock_minimo THEN 'Stock Bajo'
                                ELSE 'Stock Normal'
                            END as estado_stock
                        FROM productos p
                        WHERE p.tipo_estado = 'activo' 
                        AND (p.stock_disponible <= p.stock_minimo OR p.stock_disponible <= 0)
                        ORDER BY p.stock_disponible ASC, p.descripcion_corta
                    """)
                else:
                    cur.execute("""
                        SELECT 
                            p.id,
                            p.descripcion_corta,
                            p.descripcion_larga,
                            p.marca,
                            p.codigo_barra,
                            p.sku,
                            p.stock_disponible,
                            p.stock_minimo,
                            p.precio_venta,
                            p.costo_promedio,
                            CASE 
                                WHEN p.stock_disponible <= 0 THEN 'Sin Stock'
                                WHEN p.stock_disponible <= %s THEN 'Stock Bajo'
                                ELSE 'Stock Normal'
                            END as estado_stock
                        FROM productos p
                        WHERE p.tipo_estado = 'activo' 
                        AND p.stock_disponible <= %s
                        ORDER BY p.stock_disponible ASC, p.descripcion_corta
                    """, (stock_minimo, stock_minimo))
                
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo productos con stock bajo: {e}")
            self.conn.rollback()
            return []
    
    def get_productos_rentabilidad(self, fecha_desde, fecha_hasta, top_n=10):
        """Obtener productos más rentables"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        p.id,
                        p.descripcion_corta,
                        p.descripcion_larga,
                        p.marca,
                        p.codigo_barra,
                        p.sku,
                        SUM(v.cantidad) as total_cantidad,
                        SUM(v.total) as total_ingresos,
                        SUM(v.cantidad * p.costo_promedio) as total_costos,
                        (SUM(v.total) - SUM(v.cantidad * p.costo_promedio)) as ganancia_total,
                        ROUND(
                            CASE 
                                WHEN SUM(v.cantidad * p.costo_promedio) > 0 
                                THEN ((SUM(v.total) - SUM(v.cantidad * p.costo_promedio)) * 100.0 / SUM(v.cantidad * p.costo_promedio))
                                ELSE 0 
                            END, 2
                        ) as margen_porcentaje,
                        AVG(v.precio_unitario) as precio_promedio,
                        p.costo_promedio,
                        (AVG(v.precio_unitario) - p.costo_promedio) as margen_unitario
                    FROM venta v
                    JOIN productos p ON v.producto_id = p.id
                    WHERE DATE(v.fecha_venta) BETWEEN %s AND %s
                    AND p.costo_promedio > 0
                    GROUP BY p.id, p.descripcion_corta, p.descripcion_larga, p.marca, 
                             p.codigo_barra, p.sku, p.costo_promedio
                    HAVING SUM(v.cantidad * p.costo_promedio) > 0
                    ORDER BY margen_porcentaje DESC
                    LIMIT %s
                """, (fecha_desde, fecha_hasta, top_n))
                
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo productos rentables: {e}")
            self.conn.rollback()
            return []
    
    def get_analisis_rotacion_productos(self, fecha_desde, fecha_hasta):
        """Obtener análisis de rotación de productos"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        p.id,
                        p.descripcion_corta,
                        p.descripcion_larga,
                        p.marca,
                        p.stock_disponible,
                        p.stock_minimo,
                        COALESCE(SUM(v.cantidad), 0) as cantidad_vendida,
                        COALESCE(COUNT(DISTINCT DATE(v.fecha_venta)), 0) as dias_con_venta,
                        COALESCE(SUM(v.cantidad) / NULLIF(COUNT(DISTINCT DATE(v.fecha_venta)), 0), 0) as promedio_diario,
                        CASE 
                            WHEN p.stock_disponible > 0 AND COALESCE(SUM(v.cantidad), 0) > 0
                            THEN ROUND(p.stock_disponible / NULLIF(SUM(v.cantidad) / NULLIF(COUNT(DISTINCT DATE(v.fecha_venta)), 0), 0), 1)
                            ELSE NULL
                        END as dias_inventario,
                        CASE 
                            WHEN COALESCE(SUM(v.cantidad), 0) = 0 THEN 'Sin Rotación'
                            WHEN COALESCE(SUM(v.cantidad), 0) < p.stock_disponible * 0.1 THEN 'Rotación Baja'
                            WHEN COALESCE(SUM(v.cantidad), 0) < p.stock_disponible * 0.5 THEN 'Rotación Media'
                            ELSE 'Rotación Alta'
                        END as categoria_rotacion
                    FROM productos p
                    LEFT JOIN venta v ON p.id = v.producto_id 
                        AND DATE(v.fecha_venta) BETWEEN %s AND %s
                    WHERE p.tipo_estado = 'activo'
                    GROUP BY p.id, p.descripcion_corta, p.descripcion_larga, p.marca, 
                             p.stock_disponible, p.stock_minimo
                    ORDER BY cantidad_vendida DESC
                """, (fecha_desde, fecha_hasta))
                
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo análisis de rotación: {e}")
            self.conn.rollback()
            return []
    
    def get_estadisticas_generales_productos(self):
        """Obtener estadísticas generales de productos"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        COUNT(*) as total_productos,
                        COUNT(CASE WHEN tipo_estado = 'activo' THEN 1 END) as productos_activos,
                        COUNT(CASE WHEN stock_disponible <= 0 THEN 1 END) as productos_sin_stock,
                        COUNT(CASE WHEN stock_disponible <= stock_minimo AND stock_disponible > 0 THEN 1 END) as productos_stock_bajo,
                        COUNT(CASE WHEN stock_disponible > stock_minimo THEN 1 END) as productos_stock_normal,
                        ROUND(AVG(stock_disponible), 2) as stock_promedio,
                        ROUND(SUM(stock_disponible * costo_promedio), 2) as valor_inventario_total,
                        ROUND(SUM(stock_disponible * precio_venta), 2) as valor_venta_total
                    FROM productos
                    WHERE tipo_estado = 'activo'
                """)
                
                result = cur.fetchone()
                if result:
                    return {
                        'total_productos': result[0],
                        'productos_activos': result[1],
                        'productos_sin_stock': result[2],
                        'productos_stock_bajo': result[3],
                        'productos_stock_normal': result[4],
                        'stock_promedio': result[5],
                        'valor_inventario_total': result[6],
                        'valor_venta_total': result[7]
                    }
                return {}
        except Exception as e:
            print(f"Error obteniendo estadísticas generales: {e}")
            self.conn.rollback()
            return {}
    
    # ==================== REPORTES DE USUARIOS ====================
    
    def get_actividad_usuarios(self, fecha_desde, fecha_hasta):
        """Obtener actividad de usuarios por período"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        u.id,
                        u.nombre,
                        u.usuario,
                        u.rol,
                        COUNT(s.id) as total_sesiones,
                        COUNT(CASE WHEN s.tipo_conexion = 'login' THEN 1 END) as logins,
                        COUNT(CASE WHEN s.tipo_conexion = 'logout' THEN 1 END) as logouts,
                        COALESCE(AVG(s.tiempo_conexion), 0) as tiempo_promedio_min,
                        COALESCE(SUM(s.tiempo_conexion), 0) as tiempo_total_min,
                        MAX(s.fecha_registro) as ultima_actividad,
                        u.conectado as estado_actual
                    FROM usuarios u
                    LEFT JOIN sesiones s ON u.usuario = s.usuario 
                        AND DATE(s.fecha_registro) BETWEEN %s AND %s
                    GROUP BY u.id, u.nombre, u.usuario, u.rol, u.conectado
                    ORDER BY total_sesiones DESC, u.nombre
                """, (fecha_desde, fecha_hasta))
                
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo actividad de usuarios: {e}")
            self.conn.rollback()
            return []
    
    def get_ventas_por_usuario(self, fecha_desde, fecha_hasta):
        """Obtener ventas realizadas por usuario"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        u.id,
                        u.nombre,
                        u.usuario,
                        u.rol,
                        COUNT(DISTINCT v.comprobante_id) as total_ventas,
                        COALESCE(SUM(v.total), 0) as total_monto,
                        COALESCE(AVG(v.total), 0) as promedio_venta,
                        COUNT(v.id) as total_items,
                        COALESCE(SUM(v.cantidad), 0) as total_cantidad
                    FROM usuarios u
                    LEFT JOIN venta v ON u.id = v.usuario_id 
                        AND DATE(v.fecha_venta) BETWEEN %s AND %s
                    GROUP BY u.id, u.nombre, u.usuario, u.rol
                    ORDER BY total_monto DESC, u.nombre
                """, (fecha_desde, fecha_hasta))
                
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo ventas por usuario: {e}")
            self.conn.rollback()
            return []
    
    def get_sesiones_detalladas(self, fecha_desde, fecha_hasta, usuario_id=None):
        """Obtener sesiones detalladas por usuario"""
        try:
            with self.conn.cursor() as cur:
                if usuario_id:
                    cur.execute("""
                        SELECT 
                            s.id,
                            s.usuario,
                            u.nombre,
                            u.rol,
                            s.terminal,
                            s.local,
                            s.tipo_conexion,
                            s.fecha_registro,
                            s.fecha_logout,
                            s.tiempo_conexion,
                            CASE 
                                WHEN s.tiempo_conexion IS NULL AND s.tipo_conexion = 'login' THEN 'Activa'
                                WHEN s.tiempo_conexion IS NOT NULL THEN 'Cerrada'
                                ELSE 'Pendiente'
                            END as estado_sesion
                        FROM sesiones s
                        JOIN usuarios u ON s.usuario = u.usuario
                        WHERE u.id = %s 
                        AND DATE(s.fecha_registro) BETWEEN %s AND %s
                        ORDER BY s.fecha_registro DESC
                    """, (usuario_id, fecha_desde, fecha_hasta))
                else:
                    cur.execute("""
                        SELECT 
                            s.id,
                            s.usuario,
                            u.nombre,
                            u.rol,
                            s.terminal,
                            s.local,
                            s.tipo_conexion,
                            s.fecha_registro,
                            s.fecha_logout,
                            s.tiempo_conexion,
                            CASE 
                                WHEN s.tiempo_conexion IS NULL AND s.tipo_conexion = 'login' THEN 'Activa'
                                WHEN s.tiempo_conexion IS NOT NULL THEN 'Cerrada'
                                ELSE 'Pendiente'
                            END as estado_sesion
                        FROM sesiones s
                        JOIN usuarios u ON s.usuario = u.usuario
                        WHERE DATE(s.fecha_registro) BETWEEN %s AND %s
                        ORDER BY s.fecha_registro DESC
                    """, (fecha_desde, fecha_hasta))
                
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo sesiones detalladas: {e}")
            self.conn.rollback()
            return []
    
    def get_analisis_horarios_actividad(self, fecha_desde, fecha_hasta):
        """Obtener análisis de horarios de mayor actividad"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        EXTRACT(HOUR FROM s.fecha_registro) as hora,
                        COUNT(*) as total_sesiones,
                        COUNT(DISTINCT s.usuario) as usuarios_unicos,
                        COUNT(CASE WHEN s.tipo_conexion = 'login' THEN 1 END) as logins,
                        COUNT(CASE WHEN s.tipo_conexion = 'logout' THEN 1 END) as logouts
                    FROM sesiones s
                    WHERE DATE(s.fecha_registro) BETWEEN %s AND %s
                    GROUP BY EXTRACT(HOUR FROM s.fecha_registro)
                    ORDER BY hora
                """, (fecha_desde, fecha_hasta))
                
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo análisis de horarios: {e}")
            self.conn.rollback()
            return []
    
    def get_usuarios_inactivos(self, dias_inactividad=7):
        """Obtener usuarios inactivos por número de días"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        u.id,
                        u.nombre,
                        u.usuario,
                        u.rol,
                        u.conectado,
                        MAX(s.fecha_registro) as ultima_sesion,
                        CURRENT_DATE - MAX(DATE(s.fecha_registro)) as dias_inactivo
                    FROM usuarios u
                    LEFT JOIN sesiones s ON u.usuario = s.usuario
                    GROUP BY u.id, u.nombre, u.usuario, u.rol, u.conectado
                    HAVING MAX(s.fecha_registro) IS NULL 
                        OR CURRENT_DATE - MAX(DATE(s.fecha_registro)) >= %s
                    ORDER BY dias_inactivo DESC NULLS FIRST, u.nombre
                """, (dias_inactividad,))
                
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo usuarios inactivos: {e}")
            self.conn.rollback()
            return []
    
    def get_estadisticas_usuarios_generales(self):
        """Obtener estadísticas generales de usuarios"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        COUNT(*) as total_usuarios,
                        COUNT(CASE WHEN conectado = 'si' THEN 1 END) as usuarios_conectados,
                        COUNT(CASE WHEN conectado = 'no' THEN 1 END) as usuarios_desconectados,
                        COUNT(CASE WHEN rol = 'admin' THEN 1 END) as administradores,
                        COUNT(CASE WHEN rol = 'jefe' THEN 1 END) as jefes,
                        COUNT(CASE WHEN rol = 'supervisor' THEN 1 END) as supervisores,
                        COUNT(CASE WHEN rol = 'cajero' THEN 1 END) as cajeros,
                        COUNT(CASE WHEN rol = 'barras' THEN 1 END) as barras,
                        COUNT(CASE WHEN COALESCE(estado::text,'') = 'activo' THEN 1 END) as usuarios_activos,
                        COUNT(CASE WHEN COALESCE(estado::text,'') = 'inactivo' THEN 1 END) as usuarios_inactivos
                    FROM usuarios
                """)
                
                result = cur.fetchone()
                if result:
                    return {
                        'total_usuarios': result[0],
                        'usuarios_conectados': result[1],
                        'usuarios_desconectados': result[2],
                        'administradores': result[3],
                        'jefes': result[4],
                        'supervisores': result[5],
                        'cajeros': result[6],
                        'barras': result[7],
                        'usuarios_activos': result[8],
                        'usuarios_inactivos': result[9]
                    }
                return {}
        except Exception as e:
            print(f"Error obteniendo estadísticas generales: {e}")
            self.conn.rollback()
            return {}
    
    def get_sesiones_por_dia(self, fecha_desde, fecha_hasta):
        """Obtener sesiones agrupadas por día"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        DATE(s.fecha_registro) as fecha,
                        COUNT(*) as total_sesiones,
                        COUNT(DISTINCT s.usuario) as usuarios_unicos,
                        COUNT(CASE WHEN s.tipo_conexion = 'login' THEN 1 END) as logins,
                        COUNT(CASE WHEN s.tipo_conexion = 'logout' THEN 1 END) as logouts,
                        COALESCE(AVG(s.tiempo_conexion), 0) as tiempo_promedio
                    FROM sesiones s
                    WHERE DATE(s.fecha_registro) BETWEEN %s AND %s
                    GROUP BY DATE(s.fecha_registro)
                    ORDER BY fecha
                """, (fecha_desde, fecha_hasta))
                
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo sesiones por día: {e}")
            self.conn.rollback()
            return []
    
    # ==================== GESTIÓN DE PROVEEDORES ====================
    
    def get_proveedores(self, filtro="", estado="todos"):
        """Obtener lista de proveedores con filtros"""
        try:
            with self.conn.cursor() as cur:
                query = """
                    SELECT 
                        id, razon_social, nombre_comercial, ruc, direccion,
                        telefono, email, tipo_proveedor, categoria, condicion_pago,
                        estado, calificacion, total_compras, total_deuda, ultima_compra
                    FROM proveedores
                    WHERE 1=1
                """
                params = []
                
                if estado != "todos":
                    query += " AND estado = %s"
                    params.append(estado)
                
                if filtro:
                    query += " AND (razon_social ILIKE %s OR nombre_comercial ILIKE %s OR ruc LIKE %s)"
                    params.extend([f"%{filtro}%", f"%{filtro}%", f"%{filtro}%"])
                
                query += " ORDER BY razon_social"
                
                cur.execute(query, params)
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo proveedores: {e}")
            self.conn.rollback()
            return []
    
    def buscar_proveedor_por_ruc(self, ruc):
        """Buscar proveedor por RUC"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        id, razon_social, nombre_comercial, ruc, direccion,
                        telefono, celular, email, web, contacto_nombre,
                        contacto_cargo, contacto_telefono, contacto_email,
                        tipo_proveedor, categoria, condicion_pago, dias_credito,
                        descuento_general, banco, cuenta_bancaria, tipo_cuenta, cci,
                        tiene_licencia, numero_licencia, fecha_venc_licencia,
                        tiene_certificado_calidad, tiene_certificado_sanitario,
                        estado, calificacion, observaciones, total_compras,
                        total_deuda, ultima_compra
                    FROM proveedores
                    WHERE ruc = %s
                """, (ruc,))
                
                return cur.fetchone()
        except Exception as e:
            print(f"Error buscando proveedor por RUC: {e}")
            self.conn.rollback()
            return None
    
    def get_proveedor_por_id(self, proveedor_id):
        """Obtener proveedor por ID"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        id, razon_social, nombre_comercial, ruc, direccion,
                        telefono, celular, email, web, contacto_nombre,
                        contacto_cargo, contacto_telefono, contacto_email,
                        tipo_proveedor, categoria, condicion_pago, dias_credito,
                        descuento_general, banco, cuenta_bancaria, tipo_cuenta, cci,
                        tiene_licencia, numero_licencia, fecha_venc_licencia,
                        tiene_certificado_calidad, tiene_certificado_sanitario,
                        estado, calificacion, observaciones, total_compras,
                        total_deuda, ultima_compra
                    FROM proveedores
                    WHERE id = %s
                """, (proveedor_id,))
                
                return cur.fetchone()
        except Exception as e:
            print(f"Error obteniendo proveedor por ID: {e}")
            self.conn.rollback()
            return None
    
    def insertar_proveedor(self, datos):
        """Insertar nuevo proveedor"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    INSERT INTO proveedores (
                        razon_social, nombre_comercial, ruc, direccion,
                        telefono, celular, email, web, contacto_nombre,
                        contacto_cargo, contacto_telefono, contacto_email,
                        tipo_proveedor, categoria, condicion_pago, dias_credito,
                        descuento_general, banco, cuenta_bancaria, tipo_cuenta, cci,
                        tiene_licencia, numero_licencia, fecha_venc_licencia,
                        tiene_certificado_calidad, tiene_certificado_sanitario,
                        estado, calificacion, observaciones, usuario_creacion
                    ) VALUES (
                        %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
                        %s, %s, %s, %s, %s, %s, %s, %s, %s, %s,
                        %s, %s, %s, %s, %s, %s, %s, %s, %s, %s
                    ) RETURNING id
                """, datos)
                
                proveedor_id = cur.fetchone()[0]
                self.conn.commit()
                return proveedor_id
        except Exception as e:
            print(f"Error insertando proveedor: {e}")
            self.conn.rollback()
            raise
    
    def actualizar_proveedor(self, proveedor_id, datos):
        """Actualizar datos de proveedor"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    UPDATE proveedores SET
                        razon_social = %s, nombre_comercial = %s, ruc = %s, direccion = %s,
                        telefono = %s, celular = %s, email = %s, web = %s, contacto_nombre = %s,
                        contacto_cargo = %s, contacto_telefono = %s, contacto_email = %s,
                        tipo_proveedor = %s, categoria = %s, condicion_pago = %s, dias_credito = %s,
                        descuento_general = %s, banco = %s, cuenta_bancaria = %s, tipo_cuenta = %s, cci = %s,
                        tiene_licencia = %s, numero_licencia = %s, fecha_venc_licencia = %s,
                        tiene_certificado_calidad = %s, tiene_certificado_sanitario = %s,
                        estado = %s, calificacion = %s, observaciones = %s,
                        usuario_modificacion = %s, fecha_modificacion = NOW()
                    WHERE id = %s
                """, (*datos, proveedor_id))
                
                self.conn.commit()
                return True
        except Exception as e:
            print(f"Error actualizando proveedor: {e}")
            self.conn.rollback()
            raise
    
    def eliminar_proveedor(self, proveedor_id):
        """Eliminar proveedor (cambiar estado a inactivo)"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    UPDATE proveedores 
                    SET estado = 'inactivo', fecha_modificacion = NOW()
                    WHERE id = %s
                """, (proveedor_id,))
                
                self.conn.commit()
                return True
        except Exception as e:
            print(f"Error eliminando proveedor: {e}")
            self.conn.rollback()
            return False
    
    def get_estadisticas_proveedores(self):
        """Obtener estadísticas generales de proveedores"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        COUNT(*) as total_proveedores,
                        COUNT(CASE WHEN estado = 'activo' THEN 1 END) as proveedores_activos,
                        COUNT(CASE WHEN estado = 'inactivo' THEN 1 END) as proveedores_inactivos,
                        COUNT(CASE WHEN estado = 'bloqueado' THEN 1 END) as proveedores_bloqueados,
                        COUNT(CASE WHEN tipo_proveedor = 'nacional' THEN 1 END) as nacionales,
                        COUNT(CASE WHEN tipo_proveedor = 'internacional' THEN 1 END) as internacionales,
                        COALESCE(SUM(total_compras), 0) as total_compras_general,
                        COALESCE(SUM(total_deuda), 0) as total_deuda_general,
                        COALESCE(AVG(calificacion), 0) as calificacion_promedio
                    FROM proveedores
                """)
                
                result = cur.fetchone()
                if result:
                    return {
                        'total_proveedores': result[0],
                        'proveedores_activos': result[1],
                        'proveedores_inactivos': result[2],
                        'proveedores_bloqueados': result[3],
                        'nacionales': result[4],
                        'internacionales': result[5],
                        'total_compras_general': float(result[6]),
                        'total_deuda_general': float(result[7]),
                        'calificacion_promedio': float(result[8])
                    }
                return {}
        except Exception as e:
            print(f"Error obteniendo estadísticas de proveedores: {e}")
            self.conn.rollback()
            return {}
    
    def get_proveedores_por_categoria(self):
        """Obtener proveedores agrupados por categoría"""
        try:
            with self.conn.cursor() as cur:
                cur.execute("""
                    SELECT 
                        COALESCE(categoria, 'Sin categoría') as categoria,
                        COUNT(*) as cantidad,
                        COALESCE(SUM(total_compras), 0) as total_compras
                    FROM proveedores
                    WHERE estado = 'activo'
                    GROUP BY categoria
                    ORDER BY total_compras DESC
                """)
                
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo proveedores por categoría: {e}")
            self.conn.rollback()
            return []
    
    def get_top_proveedores(self, top_n=10, criterio='compras'):
        """Obtener top proveedores por compras o deuda"""
        try:
            with self.conn.cursor() as cur:
                if criterio == 'compras':
                    orden = "total_compras DESC"
                else:
                    orden = "total_deuda DESC"
                
                cur.execute(f"""
                    SELECT 
                        id, razon_social, nombre_comercial, ruc,
                        total_compras, total_deuda, calificacion,
                        tipo_proveedor, categoria
                    FROM proveedores
                    WHERE estado = 'activo'
                    ORDER BY {orden}
                    LIMIT %s
                """, (top_n,))
                
                return cur.fetchall()
        except Exception as e:
            print(f"Error obteniendo top proveedores: {e}")
            self.conn.rollback()
            return []

        