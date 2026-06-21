# Migración rms_desktop → VentaPOS Laravel

**Origen:** `D:\Proyectos\rms_desktop\`  
**Destino:** `D:\Proyectos\VentaPOS\`  
**Estado:** Inventario completado ✓ — Desarrollo Laravel pendiente (PHP 8.2)

---

## Mapa módulo a módulo

| Python (rms_desktop) | Laravel (VentaPOS) | Fase |
|----------------------|-------------------|------|
| `main.py` | `public/index.php` + routes | 1 |
| `ui/login_window.py` | `AuthController` + Breeze | 1 |
| `services/db_client.py` → `verificar_usuario` | `Auth::attempt` + bcrypt | 1 |
| `services/db_client.py` → `registra_sesion` | `UserSession` model | 1 |
| `ui/main_window.py` roles | Middleware `CheckRole` | 1 |
| `ui/venta_form.py` | `PosController` + Livewire/Alpine | 1 |
| `db_client.crear_comprobante_*` | `Sale` + `SaleItem` models | 1 |
| `db_client.buscar_producto_para_venta_*` | `ProductController@search` | 1 |
| `db_client.descontar_stock_producto` | `StockService::decrease` | 1 |
| `ui/producto_form.py` | `ProductController` CRUD | 1 |
| `db_client.insertar_producto` | `Product` fillable (38 campos → simplificar) | 1 |
| `ui/depto_admin.py` | `Category` jerárquica (depto/sub/clase) | 2 |
| `ui/precios_form.py` | `PriceHistory` model | 2 |
| `ui/clientes_admin.py` | `CustomerController` | 2 |
| `ui/cierre_caja_form.py` | `CashCloseController` | 2 |
| `ui/reportes_ventas_form.py` | `ReportController@sales` | 2 |
| `ui/stock_avanzado_form.py` | `StockController` | 2 |
| `ui/dashboard_form.py` | `DashboardController` | 3 |
| `ui/flejes_form.py` | Generador etiquetas PDF | 4 |
| `ui/proveedores_admin.py` | `SupplierController` | 4 |
| `config.json` | `empresas.configuracion` JSON | 1 |

---

## Fases de migración

### Fase 1 — MVP vendible (semanas 1–4)

**Objetivo:** Un cajero puede vender y el dueño ve stock/reporte.

- [ ] Laravel 11 + multi-empresa (`empresa_id`)
- [ ] Login + roles (owner, cashier)
- [ ] CRUD productos (campos esenciales del RMS)
- [ ] POS: iniciar venta, buscar producto, carrito, cobrar
- [ ] Descontar stock + movimiento
- [ ] Reporte ventas del día
- [ ] Landing marketing

**Paridad con desktop:** ~40% funcionalidad, 90% del valor para bodega.

### Fase 2 — Paridad operativa (semanas 5–8)

- [ ] Comprobantes (serie/número por terminal)
- [ ] Clientes + DNI
- [ ] Cierre de caja X/Z
- [ ] Suspender/recuperar venta
- [ ] Pagos mixtos
- [ ] Categorías jerárquicas (depto/sub/clase)
- [ ] Historial precios
- [ ] Anulación con supervisor

### Fase 3 — Reportes y dashboard

- [ ] Dashboard KPIs
- [ ] Reportes productos/usuarios
- [ ] Alertas stock
- [ ] Export Excel/PDF

### Fase 4 — Módulos avanzados

- [ ] Proveedores + órdenes de compra
- [ ] Flejes / etiquetas
- [ ] Impresión ticket térmico
- [ ] Facturación SUNAT electrónica

---

## PostgreSQL → MySQL — cambios de tipos

| PostgreSQL | MySQL (Laravel) |
|------------|-----------------|
| `SERIAL` | `BIGINT UNSIGNED AUTO_INCREMENT` |
| `NUMERIC(12,2)` | `DECIMAL(12,2)` |
| `BOOLEAN` | `TINYINT(1)` o `BOOLEAN` |
| `TEXT` | `TEXT` |
| `TIMESTAMP DEFAULT NOW()` | `$table->timestamps()` |

---

## Adaptación multi-empresa

Todas las tablas del RMS reciben `empresa_id`:

```sql
-- Ejemplo: productos desktop → productos VentaPOS
ALTER TABLE productos ADD empresa_id BIGINT UNSIGNED NOT NULL;
-- Índice: UNIQUE(empresa_id, sku), INDEX(empresa_id, codigo_barra)
```

`config.json` del desktop → `empresas.configuracion`:

```json
{
  "terminal": "06",
  "local": "Cercado 318",
  "serie_impresora": "CXFAA",
  "tipo_conexion": "USB"
}
```

---

## Exportar datos del PostgreSQL (opcional)

Si tienes el dump de Kali o puedes levantar Postgres en Windows:

```bash
# En Kali o donde esté la BD con datos
pg_dump -U rms_user -d rms_db --schema-only > rms_schema.sql
pg_dump -U rms_user -d rms_db --data-only -t productos -t usuarios > rms_data_sample.sql
```

Copia esos archivos a `D:\Proyectos\VentaPOS\legacy\` para script de importación a MySQL.

---

## Orden de lectura para Cursor (migrar código)

1. `services/db_client.py` — métodos de venta y productos
2. `ui/venta_form.py` — flujo UI del POS
3. `ui/producto_form.py` — campos del formulario
4. `ui/main_window.py` — permisos por rol
5. SQL files — esquemas complementarios
