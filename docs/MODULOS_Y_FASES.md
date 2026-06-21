# Módulos y fases del producto

## Mapa de módulos

Definición canónica en `config/permisos.php`. Cada módulo se activa por **plan** y/o **contratación explícita** en `empresa.configuracion.modulos_contratados`.

| Módulo | Nombre UI | Grupo | Requiere |
|--------|-----------|-------|----------|
| `inicio` | Inicio | Base | — |
| `pos` | POS Venta | Operación | — |
| `pos_movil` | POS Móvil | Operación | `pos` |
| `ventas` | Ventas | Operación | — |
| `cierres` | Cierre de caja | Operación | — |
| `productos` | Productos | Catálogo | — |
| `categorias` | Categorías | Catálogo | — |
| `clientes` | Clientes | CRM | — |
| `inventario` | Inventario | Inventario | — |
| `proveedores` | Proveedores | Compras | — |
| `compras` | Compras | Compras | `inventario` |
| `reportes` | Reportes | Análisis | — |
| `terminales` | Terminales | Operación | — |
| `usuario_terminales` | Usuarios y terminales | Operación | — |
| `sunat` | Facturación SUNAT | Operación | `ventas` |
| `usuarios` | Usuarios | Base | — |
| `permisos` | Permisos por rol | Base | — |
| `configuracion` | Configuración | Base | — |

## Planes comerciales

Ver `config/planes.php` y [MODELO_NEGOCIO.md](MODELO_NEGOCIO.md).

| Plan | Módulos típicos |
|------|-----------------|
| `beta` | Casi todos (prueba) |
| `basico` | POS, ventas, productos |
| `negocio` | + inventario, clientes, reportes |
| `pro` | + compras, SUNAT |

## Fases de implementación

### Fase 1 — Core POS ✅

- Multi-empresa, login, roles básicos
- POS escritorio, productos, categorías, clientes
- Ventas, ticket, medios de pago, pago mixto
- Ventas suspendidas (HOLD)
- Super admin panel

### Fase 2 — Operación avanzada ✅

- Inventario (entrada, salida, ajuste, kardex)
- Cierres de caja X/Z
- Terminales y asignación usuario-terminal
- Proveedores y compras con recepción de stock
- Reportes ventas, inventario, compras
- Export CSV/Excel
- Onboarding guiado

### Fase 3 — SUNAT 🔄 En curso

| Entregable | Estado |
|------------|--------|
| Campos `sunat_*` en comprobantes | ✅ |
| Configuración por empresa (simulación / producción) | ✅ |
| Emisión automática al cobrar | ✅ |
| XML UBL simplificado + almacenamiento local | ✅ |
| QR PNG en ticket (representación impresa) | ✅ |
| Badges CPE en listado y detalle ventas | ✅ |
| Cliente OSE genérico (`OseClient`) | ✅ |
| Almacenamiento CDR | ✅ |
| Anulación (simulación + stub producción) | ✅ |
| Tests unitarios y feature simulación | ✅ |
| Firma digital XML con certificado .pem | ⏳ Pendiente |
| Adaptador OSE específico (Nubefact, etc.) | ⏳ Pendiente |
| UBL 2.1 completo SUNAT | ⏳ Pendiente |

### Fase 4 — Producción y escala ⏳

- Integración OSE/PSE certificada
- Cola de reintentos SUNAT
- Multi-sucursal avanzado
- API pública / integraciones

## Extras opcionales del plan (add-ons)

Contratados **solo por el super admin** en `/admin/empresas/{id}/extras`, igual que los módulos. Definición en `config/extras.php`. Precio referencia: **S/ 9/mes** cada uno.

| Extra | Clave | Requiere módulo |
|-------|-------|-----------------|
| Resumen WhatsApp | `whatsapp_resumen` | ventas |
| Lotes y vencimientos | `lotes_vencimiento` | inventario |
| Categorías jerárquicas | `categorias_jerarquicas` | categorias |
| Flejes / etiquetas | `flejes` | productos |

**Incluido con módulo ventas (sin extra):** dashboard KPI en `/app/ventas`.

El dueño de la empresa ve en Configuración qué extras tiene activos (solo lectura) y puede configurar el teléfono WhatsApp si contrató ese extra.

Al crear empresa, los extras inician desactivados. Botón «Aplicar sugeridos por tipo» activa los de `config/extras.php` → `sugeridos_por_tipo` (ej. farmacia → lotes).

## Tarifas comerciales (super admin)

Precios editables en **Admin → Tarifas y precios** (`/admin/tarifas`). Se guardan en `storage/app/tarifas.json` y se aplican en runtime sobre `config/planes.php`, `config/permisos.php` y `config/extras.php`.

Afecta: landing de precios, alta/edición de empresas, módulos contratados, extras y estimados mensuales.

## Límites de usuarios por plan

Cupo efectivo = **plan base** + **bonificados** (sin costo) + **extras de pago** (add-on).

| Plan | Base | Gestión flexible |
|------|------|------------------|
| Beta | 3 | Admin → Empresas → **Cupos** |
| Básico | 4 | Bonificados y add-on por empresa |
| Negocio | 8 | Bonificados y add-on por empresa |
| Pro | 15 | Bonificados y add-on por empresa |

Ruta admin: `/admin/contratacion` con pestañas Resumen · Módulos · Extras · Cupos. Incluye plan **Beta** junto a Básico, Negocio y Pro (`planes_disponibles`).

El dueño ve el desglose en Usuarios; no puede modificar cupos. Validación al crear o reactivar usuarios.

## Rutas principales por módulo

| Módulo | Prefijo / rutas |
|--------|-----------------|
| POS | `/app/pos`, `/app/pos/movil`, `/app/pos/ticket/{id}` |
| Ventas | `/app/ventas` |
| Flejes | `/app/flejes` (extra `flejes`) |
| Vencimientos | `/app/inventario/vencimientos` (extra `lotes_vencimiento`) |
| SUNAT | `/app/sunat` |
| Compras | `/app/compras` |
| Admin | `/admin/empresas`, `/admin/contratacion` |
| Reportes | `/app/reportes` |

## Middleware relevante

| Middleware | Uso |
|------------|-----|
| `auth` | Usuario logueado |
| `empresa` | Contexto tenant |
| `onboarding` | Bloquea app hasta completar setup |
| `modulo:x` | Verifica módulo contratado + rol |
| `dueno` | Solo administrador empresa |

## Referencias

- [ARQUITECTURA.md](ARQUITECTURA.md)
- [SUNAT.md](SUNAT.md)
- [CASOS_DE_USO.md](CASOS_DE_USO.md)
