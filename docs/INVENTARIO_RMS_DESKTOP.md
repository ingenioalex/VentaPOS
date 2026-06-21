# Inventario completo — rms_desktop (Python/Kali)

**Origen:** `D:\Proyectos\rms_desktop\`  
**Stack original:** PySide6 (Qt) · PostgreSQL · psycopg2  
**BD:** `rms_db` · usuario `rms_user`  
**Tipo:** Aplicación de escritorio mono-empresa (una tienda/local)

---

## Módulos encontrados

| # | Módulo | Archivo UI | Prioridad Laravel | Fase |
|---|--------|------------|-------------------|------|
| 1 | Login + sesión terminal | `login_window.py` | Alta | 1 |
| 2 | Menú principal por roles | `main_window.py` | Alta | 1 |
| 3 | **POS Venta** | `venta_form.py` (~3900 líneas) | **Crítica** | 1 |
| 4 | Productos | `producto_form.py` | Alta | 1 |
| 5 | Categorías (Depto/Subdepto/Clase) | `depto_admin.py` | Media | 2 |
| 6 | Actualizar precios | `precios_form.py` | Media | 2 |
| 7 | Clientes | `clientes_admin.py` | Media | 2 |
| 8 | Cierres de caja | `cierre_caja_form.py`, `cierres_admin.py` | Alta | 2 |
| 9 | Dashboard | `dashboard_form.py` | Media | 3 |
| 10 | Stock avanzado | `stock_avanzado_form.py` | Media | 2 |
| 11 | Movimientos stock | `movimiento_stock_dialog.py` | Media | 2 |
| 12 | Reportes ventas | `reportes_ventas_form.py` | Alta | 2 |
| 13 | Reportes productos | `reportes_productos_form.py` | Media | 3 |
| 14 | Reportes usuarios | `reportes_usuarios_form.py` | Baja | 3 |
| 15 | Usuarios admin | `user_admin.py` | Alta | 1 |
| 16 | Proveedores + OC | `proveedores_admin.py` | Baja | 4 |
| 17 | Flejes / códigos barras | `flejes_form.py`, `flejes_vb.py` | Baja | 4 |
| 18 | Config sistema | `config_sistema_form.py` | Media | 2 |
| 19 | Pagos mixtos | `pagos_mixtos_dialog.py` | Media | 2 |
| 20 | Supervisor / anulaciones | `supervisor_dialog.py` | Media | 2 |

**Núcleo de negocio:** módulos 1, 2, 3, 4, 8, 12.

---

## Roles del sistema original

| Rol | Acceso |
|-----|--------|
| `admin` | Todo |
| `cajero` | Solo POS |
| `supervisor` | POS + cierres caja |
| `jefe` | POS + dashboard + reportes + flejes |
| `barras` | Solo flejes/etiquetas |
| `digitador` | Categorías + productos + proveedores |
| `tesorero`, `test`, `generico` | Configurables |

**Laravel VentaPOS (simplificado fase 1):** `super_admin`, `owner`, `cashier`, `supervisor`

---

## Tablas PostgreSQL identificadas

| Tabla | Uso |
|-------|-----|
| `usuarios` | Login, roles, estado conectado |
| `productos` | ~38 campos (SKU, barra, precios, stock, marca, IGV…) |
| `producto_precio` | Historial de precios |
| `departamento` / `subdepartamento` / `clase` | Jerarquía categorías |
| `comprobantes` | Cabecera venta (serie, número, tipo doc) |
| `comprobante_detalle` | Líneas de venta |
| `venta` | Registro analítico por línea |
| `clientes` | DNI/RUC, historial compras |
| `cierres_caja` | Cierre X/Z por usuario/terminal |
| `movimientos_stock` | Entradas/salidas/ajustes |
| `alertas_stock` | Stock bajo / sin stock |
| `proveedores` + relacionadas | Compras (fase 4) |
| `sesiones` | Terminal + local por login |

---

## Campos clave de `productos` (migrar a Laravel)

```
sku, codigo_barra, descripcion_larga, descripcion_corta, nombre
costo_compra, costo_promedio, valor_venta, precio_venta
afecto_igv, valor_igv, stock_contable, stock_disponible, stock_minimo
unidad_medida, marca, registro_sanitario, pesable
clase_id, categoria, tipo_producto
campo1, campo2, campo3  ← campos custom ya existentes
```

**Nota:** El RMS desktop ya tiene `campo1/2/3` — equivalente a nuestro sistema de campos custom.

---

## Lógica POS (`venta_form.py`) — replicar en web

1. **INICIAR** comprobante (serie/número por terminal)
2. Escanear/buscar por código barra, SKU o descripción
3. Agregar líneas con cantidad, IGV 18%, precio unitario
4. **SUSPENDER** venta → guarda en JSON (`pos_venta_hold.json`)
5. **RECUPERAR** venta suspendida
6. **CANCELAR** comprobante
7. Asociar cliente (DNI)
8. Medios de pago: efectivo, tarjeta, mixto
9. Descontar `stock_disponible` al finalizar
10. Cierre rápido de caja desde POS

---

## Diferencias: Desktop → VentaPOS Web SaaS

| Aspecto | rms_desktop | VentaPOS Laravel |
|---------|-------------|------------------|
| Interfaz | PySide6 ventanas | Blade + Alpine, responsive |
| BD | PostgreSQL local | MySQL en VPS |
| Empresas | Una (`rms_db`) | Multi-tenant (`tenant_id`) |
| Config | `config.json` (terminal, local) | Settings por tenant |
| Contraseñas | Texto plano ⚠️ | bcrypt (Laravel) |
| Impresora | USB serie (`config_impresora.json`) | Fase 3 (ticket PDF/browser print) |
| Offline | Nativo desktop | PWA fase 2 |

---

## Archivos de referencia para migración

| Archivo | Líneas aprox. | Qué extraer |
|---------|---------------|-------------|
| `services/db_client.py` | 2700+ | Toda la lógica SQL |
| `ui/venta_form.py` | 3900+ | Flujo POS completo |
| `ui/producto_form.py` | — | Campos producto |
| `create_venta_table.sql` | — | Esquema venta |
| `create_cierres_caja_table.sql` | — | Esquema cierres |
| `create_stock_movements_table.sql` | — | Esquema stock |
| `sql_proveedores.sql` | — | Esquema proveedores |

---

## Seguridad — ACCIÓN REQUERIDA

`db_client.py` tiene credenciales hardcodeadas. **No subir a GitHub público.**  
En Laravel usar `.env` para BD. Rotar contraseña de `rms_user` si el repo se expone.
