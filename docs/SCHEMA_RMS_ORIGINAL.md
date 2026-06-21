# Esquema original RMS — `RMS_bd/Basedatos/`

**Fuente canónica:** `D:\Proyectos\rms_desktop\RMS_bd\Basedatos\rms_db.sql`  
**Metadatos:** `Metadatos_de_la_Base_de_Datos_RMS.csv`  
**Diagrama ER:** `diagrama_er_rms.png` / `.pdf`

---

## Tablas del diseño original (18 entidades)

| # | Tabla | Propósito |
|---|-------|-----------|
| 1 | `sesiones` | Login/logout por terminal y local |
| 2 | `usuarios` | Usuarios y roles |
| 3 | `clientes` | DNI/RUC clientes |
| 4 | `departamento` | Categoría nivel 1 |
| 5 | `subdepartamento` | Categoría nivel 2 |
| 6 | `clase` | Categoría nivel 3 |
| 7 | `productos` | Catálogo completo (~40 campos) |
| 8 | `producto_precio` | Historial cambio precios |
| 9 | `terminal` | Cajas, series, correlativos |
| 10 | `config_impresora` | Config impresora por terminal |
| 11 | `comprobantes` | Boleta, factura, NC, etc. |
| 12 | `detalle_comprobante` | Líneas del comprobante |
| 13 | `venta` | Registro analítico por línea vendida |
| 14 | `cierre_caja` | Cierre X / Z |
| 15 | `retiro_efectivo` | Retiro cuando caja > S/2000 |
| 16 | `inventario_movimiento` | Entrada/salida/venta stock |

---

## Triggers y lógica automática (PostgreSQL)

| Trigger | Función | Qué hace |
|---------|---------|----------|
| `trg_set_fecha_venta_info` | `set_fecha_venta_info()` | Calcula año, mes, semana en `venta` |
| `trg_restar_stock` | `fn_restar_stock()` | Resta stock + inserta `inventario_movimiento` |
| `trg_comprobante_retiro` | `fn_generar_comprobante_retiro()` | Si retiro > S/2000 → comprobante tipo RE |
| `trg_actualizar_correlativo` | `fn_actualizar_correlativo_terminal()` | Incrementa número en `terminal` |

**En VentaPOS Laravel:** esta lógica va en **Services** (PHP), no triggers — más portable en MySQL multi-tenant.

---

## Vistas y funciones

- `vista_reporte_z` — reporte Z por terminal (⚠️ referencia tabla `ventas` plural, bug)
- `vista_detalle_cierre_caja` — detalle cierres
- `fn_exportar_cierre_json(id)` — export JSON cierre

---

## Modificaciones posteriores (parches fuera de rms_db.sql)

Archivos en `rms_desktop/` que **añadieron o cambiaron** cosas:

| Archivo | Cambio |
|---------|--------|
| `create_cierres_caja_table.sql` | Tabla `cierres_caja` (nombre distinto a `cierre_caja`) |
| `create_stock_movements_table.sql` | `movimientos_stock` + `alertas_stock` |
| `create_venta_table.sql` | Recrea/refuerza tabla `venta` |
| `sql_proveedores.sql` | Módulo proveedores + órdenes compra |
| `db_client.py` | Roles extra: `barras`, `digitador`, `generico` |
| `db_client.py` | Lógica comprobantes en Python (paralela a triggers) |

**Conclusión:** La BD en producción probablemente mezcla `rms_db.sql` + parches manuales. VentaPOS unificará todo en un esquema limpio.

---

## Adaptación VentaPOS (mejoras sobre el original)

| Original RMS | VentaPOS mejorado |
|--------------|-------------------|
| 1 empresa, 1 BD | Multi-empresa (`empresa_id`) |
| PostgreSQL | MySQL 8 (hosting Perú barato) |
| Triggers PG | `SaleService`, `StockService` en PHP |
| Password texto plano | bcrypt |
| `sku VARCHAR(10)` | `sku VARCHAR(50)` |
| Roles mezclados | Roles claros: owner, cashier, supervisor |
| Desktop only | Web + móvil |
| Impresora USB obligatoria | Ticket PDF / print browser (fase 2) |

### Tablas que se mantienen (concepto)

```
empresas              ← NUEVO (SaaS)
usuarios              ← usuarios
sesiones              ← sesiones
departamentos         ← departamento
subdepartamentos      ← subdepartamento
clases                ← clase
productos             ← productos
producto_precio       ← historial precios
clientes              ← clientes
terminales            ← terminal
comprobantes          ← cabecera venta
detalle_comprobante   ← líneas venta
venta                 ← analítica (fase 2)
inventario_movimiento ← movimientos stock
cierre_caja           ← cierres
retiro_efectivo       ← retiros
```

### Fase 1 — mínimo para vender

Solo: `empresas`, `usuarios`, `productos`, `comprobantes`, `detalle_comprobante`, `inventario_movimiento`

El resto se migra en fases 2–4.

---

## Cargar esquema original (referencia)

### En Kali (PostgreSQL)
```bash
sudo -u postgres psql -c "CREATE USER rms_user WITH PASSWORD 'tu_clave';"
sudo -u postgres psql -c "CREATE DATABASE rms_db OWNER rms_user;"
psql -U rms_user -d rms_db -f /ruta/rms_db.sql
# Luego aplicar parches si los necesitas
```

### En Windows (solo consulta legacy)
- Instalar PostgreSQL Windows, o
- Usar Docker: `docker run -d -p 5432:5432 -e POSTGRES_PASSWORD=... postgres`
- Importar `rms_db.sql` para comparar datos

**No es obligatorio** para VentaPOS web — el esquema MySQL ya está en `database/schema_rms_completo.sql`.
