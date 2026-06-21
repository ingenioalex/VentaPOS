# VentaPOS — Modelo de negocio

## Propuesta de valor

> **"Deja el cuaderno. Vende más rápido, controla tu stock desde el celular y sabe cuánto ganaste hoy."**

No vendes "software". Vendes **control diario** con soporte local en Chiclayo.

## Cliente ideal (ICP)

| Perfil | Dolor principal | Dónde encontrarlo |
|--------|-----------------|-------------------|
| Bodega mediana | No sabe stock real, pierde productos | Gómez Ferrer, mercados, AA.HH. |
| Ferretería pequeña | Muchos SKUs, precios distintos por unidad | Av. Balta, Elías Aguirre |
| Farmacia de barrio | Vencimientos, control de lotes | Barrios residenciales |

## Producto

**VentaPOS** = POS web + inventario + reportes en la nube (multi-empresa).

### MVP (fase 1 — semanas 1–6)

- Login multi-empresa (`empresa_id`)
- Productos y categorías
- Plantillas por rubro (bodega / ferretería / farmacia)
- Campos personalizados (hasta 3 en plan básico)
- Venta rápida (POS)
- Descuento automático de stock
- Reporte ventas del día / mes
- Alerta stock bajo
- Roles: dueño / cajero
- Export Excel
- Escáner código barras (móvil, HTTPS)

### Fase 2 (mes 3–4)

- Resumen diario WhatsApp
- PWA offline (ventas sin internet)
- Import masivo CSV

### Fase 3 (mes 5+)

- Facturación electrónica SUNAT (plan Pro)
- Compras a proveedor
- Multi-sucursal

## Precios (Chiclayo 2026)

| Plan | Instalación | Mensual | Incluye |
|------|-------------|---------|---------|
| **Beta** | Gratis | Gratis 2 meses | 3 usuarios, feedback semanal |
| **Básico** | S/99 arranque | S/29/mes | 4 usuarios (admin + supervisor + 2 cajeros) |
| **Negocio** | S/99 arranque | S/45/mes | 8 usuarios, inventario, compras |
| **Pro** | S/99 arranque | S/59/mes | 15 usuarios, SUNAT, multi-terminal |

**Pago:** Yape, Plin, transferencia. Culqi cuando haya 10+ clientes.

## Arquitectura comercial

```
TÚ (proveedor SaaS)
  │
  ├── Landing ventapos.pe → captura leads
  ├── App app.ventapos.pe → clientes operan
  └── Soporte WhatsApp (9am–7pm)
        │
        ├── Empresa A → datos aislados por empresa_id
        ├── Empresa B → datos aislados por empresa_id
        └── Empresa C → datos aislados por empresa_id
```

**Una sola aplicación, muchas empresas.** No instalación por cliente.

## Diferenciación vs competencia

| VentaPOS | Otros |
|----------|-------|
| Soporte presencial Chiclayo | Solo call center Lima |
| Configuración incluida en instalación | "Aprende solo" |
| Plantillas por rubro local | Software genérico |
| Precio accesible PYME | Planes caros o hardware obligatorio |

## Control de personalización (farmacia ≠ ferretería)

1. **Plantilla de rubro** al crear empresa (campos y categorías preset)
2. **Campos custom** por empresa (JSON, sin código nuevo)
3. **Módulos on/off** (vencimientos, unidades, mayorista)
4. **Desarrollo a medida** → cotización aparte (S/150–500)

**Política:** "Plan incluye plantilla + 3 campos custom. Extras se cotizan."

## Métricas objetivo

| Mes | Clientes pagando | MRR objetivo |
|-----|------------------|--------------|
| 2 | 0 (beta) | S/0 |
| 3 | 2 | S/78 |
| 4 | 5 | S/195 |
| 6 | 12 | S/468 |
| 12 | 30 | S/1,170 |

*MRR = ingreso mensual recurrente (sin instalaciones)*

## Canales de adquisición

1. **Presencial** (70% esfuerzo inicial): visitas Gómez Ferrer con tablet demo
2. **Referidos** (20%): 1 mes gratis por cliente referido
3. **Web/SEO** (10% inicial, crece): landing + Google "inventario bodega Chiclayo"

## Costos y margen

- Costo hosting por cliente (50 clientes): ~S/1/mes
- Plan Básico S/39/mes → margen bruto ~S/37/mes
- Instalación S/250 → paga 6+ meses de tu tiempo de onboarding

**Punto de equilibrio:** ~2 clientes cubren hosting (~S/80/mes fijo).
