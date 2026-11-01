# Identidad de marca — VentasÁgil

Sistema visual de **ventasagil.com**: símbolo (icono) y logotipo (texto) separados, para usar juntos o por separado.

## Concepto

| Pieza | Qué es | Uso |
|-------|--------|-----|
| **Símbolo / isotipo** | Rayo blanco sobre placa teal redondeada | Favicon, WhatsApp, app icon, avatar |
| **Logotipo / wordmark** | Tipografía «Ventas» + «Ágil» | Textos largos, facturas solo texto |
| **Imagotipo** | Símbolo + logotipo | Cabecera web, presentaciones, sellos |

El rayo comunica **velocidad / agilidad** del cobro; el teal es el color primario ya usado en la app (`#0f766e`).

## Colores

| Token | Hex | Uso |
|-------|-----|-----|
| Teal primario | `#0f766e` | Botones, «Ágil», placa |
| Teal claro | `#14b8a6` | Degradado del icono |
| Tinta | `#0f172a` | «Ventas», texto |
| Blanco | `#ffffff` | Rayo, fondos claros |
| Negro sello | `#000000` | Facturas / 1 tinta |

## Tipografía

Sans-serif moderna y legible a distancia: **Segoe UI** (Windows) / **Inter** / system-ui. En PNG se rasteriza con Segoe UI Bold.

## Archivos (`public/brand/`)

### SVG (fuente escalable — preferir en web)

| Archivo | Descripción |
|---------|-------------|
| `icon.svg` | Símbolo color (favicon SVG) |
| `icon-mono.svg` | Símbolo tinta oscura |
| `icon-light.svg` | Placa blanca + rayo teal |
| `icon-white.svg` | Variante clara |
| `icon-stamp.svg` | Sello 1 tinta |
| `icon-transparent.svg` | Solo rayo teal |
| `wordmark.svg` / `wordmark-mono.svg` | Solo texto |
| `logo-horizontal.svg` | Imagotipo horizontal (cabecera) |
| `logo-horizontal-on-dark.svg` | Para fondos oscuros |
| `logo-horizontal-mono.svg` | Facturas / impresión |
| `logo-vertical.svg` / `logo-vertical-mono.svg` | Apilado |
| `logo.svg` | Alias del horizontal (compatibilidad) |

### PNG / ICO (raster)

| Archivo | Uso |
|---------|-----|
| `favicon.ico` | Pestañas (16/32/48) |
| `apple-touch-icon.png` | iOS 180×180 |
| `icon-192.png` / `icon-512.png` | PWA / Android |
| `og-image.png` | Open Graph 1200×630 |
| `png/icon-{16…1024}.png` | Exportes varios tamaños |
| `png/whatsapp-profile-512.png` | Foto de perfil WhatsApp |
| `png/whatsapp-profile-640.png` | Idem alta |
| `png/logo-horizontal*.png` | Horizontal color / oscuro / mono |
| `png/logo-vertical*.png` | Vertical |
| `png/wordmark*.png` | Solo texto |
| `png/sello-factura.png` | Sello con margen blanco |
| `png/icon-stamp-512.png` | Icono sello |

También: `public/favicon.ico` (copia para la raíz del sitio).

## Cuándo usar cada versión

```
Web cabecera (fondo claro)  → logo-horizontal.svg
Web cabecera (fondo oscuro) → logo-horizontal-on-dark.svg
Móvil / menú colapsado      → icon.svg
Favicon                     → favicon.ico + icon.svg
WhatsApp / redes            → png/whatsapp-profile-512.png
Factura / PDF 1 tinta       → logo-horizontal-mono.svg o icon-stamp.svg
Sello físico                → png/sello-factura.png (imprimir)
OG / compartir enlace       → og-image.png
```

## Regenerar rasters

Tras editar el símbolo en el script:

```bash
python scripts/generar-brand-assets.py
```

Requiere Pillow (`python -m pip install pillow`).

## Layouts

Los favicons se incluyen vía `resources/views/partials/brand-icons.blade.php` en layouts guest, app y admin.
