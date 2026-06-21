# VentaPOS — Nomenclatura de base de datos (español)

Convención oficial para tablas y columnas. Alineada con `rms_db.sql` original donde aplica.

---

## Reglas generales

| Regla | Ejemplo |
|-------|---------|
| Tablas en **plural** o nombre RMS original | `empresas`, `productos`, `cierre_caja` |
| Columnas en **español snake_case** | `empresa_id`, `nombre`, `creado_en` |
| FK siempre `{tabla_singular}_id` | `empresa_id`, `producto_id`, `usuario_id` |
| Booleanos como `activo`, `conectado` | `activo TINYINT(1)` |
| Fechas `creado_en`, `actualizado_en` | En lugar de `created_at` |
| Términos técnicos SaaS en español | `empresa` no `tenant` |

---

## Tablas — mapa inglés → español

| Antes (inglés) | Ahora (español) | Notas |
|----------------|-----------------|-------|
| `tenants` | **`empresas`** | Multi-empresa SaaS |
| `users` | **`usuarios`** | Igual que RMS desktop |
| `user_sessions` | **`sesiones`** | Login por terminal |
| `departments` | **`departamentos`** | RMS original |
| `subdepartments` | **`subdepartamentos`** | RMS original |
| `product_classes` | **`clases`** | RMS original |
| `products` | **`productos`** | RMS original |
| `product_price_history` | **`producto_precio`** | RMS original |
| `customers` | **`clientes`** | RMS original |
| `sales` | **`comprobantes`** | Boleta/factura (RMS) |
| `sale_items` | **`detalle_comprobante`** | RMS original |
| `stock_movements` | **`inventario_movimiento`** | RMS original |
| `cash_closures` | **`cierre_caja`** | RMS original |
| `cash_withdrawals` | **`retiro_efectivo`** | RMS original |
| `leads` | **`prospectos`** | Landing / beta |

---

## Columnas frecuentes

| Inglés | Español |
|--------|---------|
| `tenant_id` | **`empresa_id`** |
| `name` | **`nombre`** |
| `slug` | **`codigo`** (URL: bodega-juan) |
| `business_type` | **`tipo_negocio`** |
| `phone` | **`telefono`** |
| `address` | **`direccion`** |
| `settings` | **`configuracion`** (JSON) |
| `is_active` | **`activo`** |
| `password` | **`clave`** o `password` (Laravel auth) |
| `role` | **`rol`** |
| `barcode` | **`codigo_barra`** |
| `sale_price` | **`precio_venta`** |
| `stock_available` | **`stock_disponible`** |
| `payment_method` | **`medio_pago`** |
| `created_at` | **`creado_en`** |
| `updated_at` | **`actualizado_en`** |

---

## Modelos Laravel (código PHP)

El código PHP puede usar clases en inglés (`Empresa`, `Producto`) apuntando a tablas españolas:

```php
class Empresa extends Model {
    protected $table = 'empresas';
}

class Producto extends Model {
    protected $table = 'productos';
}
```

---

## Subdominios y rutas (pueden seguir en español)

- `bodega-juan.ventapos.pe` → `empresas.codigo = 'bodega-juan'`
- Middleware: `IdentificarEmpresa` (lee subdominio o sesión)
