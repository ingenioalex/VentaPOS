# VentaPOS — Itinerario de construcción (8 semanas)

## Resumen

| Hito | Semana | Fecha objetivo |
|------|--------|----------------|
| Entorno + Laravel base | 1 | 6–13 jun 2026 |
| Productos + inventario | 2 | 13–20 jun |
| POS ventas | 3 | 20–27 jun |
| Reportes + usuarios | 4 | 27 jun – 4 jul |
| Pulido + onboarding | 5 | 4–11 jul |
| **Beta 3 clientes** | 6 | 11–18 jul |
| Estabilización | 7 | 18–25 jul |
| **Lanzamiento comercial** | 8 | 25 jul – 1 ago |

---

## Semana 1 — Fundación (EN CURSO)

### Desarrollo (Cursor / código)

| Día | Tarea | Entregable | Estado |
|-----|-------|------------|--------|
| 1 | Instalar PHP 8.2 + Composer + Laragon | Entorno listo | ☐ Alex |
| 1 | `composer create-project` + git | Proyecto Laravel | ☐ |
| 2 | Multi-empresa: `empresas`, middleware `IdentificarEmpresa` | Aislamiento datos | ☐ |
| 2 | Auth login (dueño/cajero) | Login funcional | ☐ |
| 3 | Landing: home, precios, contacto, beta | `ventapos.pe` diseño | ☐ |
| 4 | Panel super-admin: crear empresa | Alta manual en `empresas` | ☐ |
| 5 | Deploy staging (VPS o Laragon tunel) | URL demo pública | ☐ |

### Negocio (Alex)

| Tarea | Estado |
|-------|--------|
| Reservar dominio ventapos.pe | ☐ |
| Lista 10 negocios para visitar | ☐ |
| Crear WhatsApp Business | ☐ |
| Confirmar 3 candidatos beta | ☐ |

---

## Semana 2 — Productos e inventario

| Día | Entregable |
|-----|------------|
| 1–2 | CRUD productos, categorías, stock |
| 3 | Plantillas rubro: bodega, ferretería, farmacia |
| 4 | Campos personalizados (JSON) por tenant |
| 5 | Import CSV productos |

**Prueba:** 50 productos en 2 tenants distintos.

---

## Semana 3 — POS ventas

| Día | Entregable |
|-----|------------|
| 1–2 | Pantalla venta: buscar + carrito + total |
| 3 | Descuento stock automático al vender |
| 4 | Historial ventas del día |
| 5 | Escáner código barras (html5-qrcode) |

**Prueba:** 20 ventas simuladas en un día.

---

## Semana 4 — Reportes y permisos

| Día | Entregable |
|-----|------------|
| 1–2 | Reporte ventas día/semana/mes |
| 3 | Alerta stock bajo |
| 4 | Roles dueño / cajero |
| 5 | Ajustes empresa (logo, IGV 18%, datos) |

---

## Semana 5 — Preparación beta

| Día | Entregable |
|-----|------------|
| 1–2 | Wizard onboarding primera vez |
| 3 | Export Excel ventas/stock |
| 4 | Responsive móvil + bugs |
| 5 | Guía usuario PDF 1 página |

**Prueba interna:** operar como bodega 3 días seguidos.

---

## Semana 6 — Beta real (3 clientes)

| Día | Entregable |
|-----|------------|
| 1 | Alta 3 empresas + migración datos |
| 2–4 | Soporte diario, registro bugs |
| 5 | Ajustes según feedback |

---

## Semana 7 — Estabilización

- Fixes críticos beta
- Backups automáticos
- Monitoreo uptime
- Testimonios en landing

---

## Semana 8 — Lanzamiento

- Precios finales publicados
- 10 visitas comerciales Chiclayo
- Meta: **2 clientes pagando** además de beta

---

## Migración desde Python (VentaPOS original)

Si encuentras el proyecto Python, mapear:

| Módulo Python (probable) | Equivalente Laravel |
|--------------------------|---------------------|
| Modelo Producto | `Product` model + migration |
| Ventas / tickets | `Sale`, `SaleItem` |
| Usuarios | Laravel Breeze/Fortify + roles |
| Reportes | Controllers + export Excel |
| Config tienda | `Tenant` settings JSON |

**Comparte la ruta del Python** para migración feature-by-feature.
