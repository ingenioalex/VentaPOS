# SEO — Posicionamiento en Google sin coste

Guía de ingeniería de posicionamiento (SEO) para **ventasagil.com**. El código ya incluye base técnica; el resto es trabajo continuo gratuito.

## Qué ya está implementado en el sitio

| Elemento | Ruta / archivo |
|----------|----------------|
| Títulos y meta descripción por página | `config/seo.php` + `partials/seo-head.blade.php` |
| Open Graph (WhatsApp, Facebook) | Mismo partial |
| URL canónica | Automática por página |
| `sitemap.xml` | `https://ventasagil.com/sitemap.xml` |
| `robots.txt` | Ruta Laravel + `public/robots.txt` en VPS (nginx sirve el estático primero) |
| Schema.org (Organization + Software) | Páginas landing |
| FAQ con schema FAQPage | `/preguntas-frecuentes` |
| Enlaces internos | Menú + footer |

## Pasos obligatorios (gratis) — hazlos tú

### 1. Google Search Console (prioridad #1)

1. Entra a [Google Search Console](https://search.google.com/search-console).
2. Añade la propiedad **ventasagil.com** (prefijo URL o dominio).
3. Verifica con registro DNS en Hostinger (método recomendado) o archivo HTML.
4. Envía el sitemap: `https://ventasagil.com/sitemap.xml`
5. Revisa en 1–2 semanas: impresiones, clics, errores de indexación.

### 2. Google Business Profile (local Chiclayo)

Si atiendes clientes en Chiclayo:

1. Crea perfil en [Google Business](https://business.google.com).
2. Categoría: *Proveedor de software* o *Servicio de consultoría informática*.
3. Zona: Chiclayo, Lambayeque.
4. Web: https://ventasagil.com
5. Pide reseñas a clientes beta (con moderación).

### 3. Bing Webmaster Tools

[https://www.bing.com/webmasters](https://www.bing.com/webmasters) — importa desde Search Console en 2 clics.

## Palabras clave objetivo (Perú)

Enfócate en intención comercial + local:

| Prioridad | Keyword |
|-----------|---------|
| Alta | punto de venta bodega peru |
| Alta | sistema ventas inventario bodega |
| Alta | pos chiclayo |
| Media | software bodega peru |
| Media | facturacion electronica bodega sunat |
| Media | programa inventario minimarket |
| Long-tail | punto de venta en la nube chiclayo |

Usa estas frases de forma natural en:

- Título H1 de inicio (ya orientado)
- Página FAQ
- Textos de contacto y precios
- Publicaciones futuras (blog, redes)

## Contenido gratuito que posiciona (plan 90 días)

| Semana | Acción gratuita |
|--------|-----------------|
| 1 | Search Console + sitemap enviado |
| 2 | Perfil Google Business completo |
| 3 | 1 post Facebook/LinkedIn: «POS para bodegas en Chiclayo» con enlace |
| 4 | Responder en grupos locales (emprendedores Chiclayo) — sin spam |
| 6 | Añadir 2 preguntas más a FAQ según dudas reales de prospectos |
| 8 | Video corto demo POS (YouTube) con enlace en descripción |
| 12 | Pedir 3 reseñas Google de clientes beta |

## Enlaces entrantes (backlinks gratis)

- Perfil LinkedIn personal con enlace a ventasagil.com
- Directorios Perú: páginas amarillas digitales, cámaras de comercio locales
- GitHub / portafolio si publicas caso de éxito
- Firma de correo y WhatsApp Business con URL

## Qué NO hacer

- Comprar enlaces o «posicionamiento garantizado» barato
- Duplicar el mismo texto en muchas páginas (penalización)
- Ocultar texto o rellenar keywords sin sentido
- Indexar `/app/` o panel admin (ya bloqueados en robots)

## Medir resultados

En Search Console, cada mes revisa:

- **Impresiones** (cuántas veces apareces en Google)
- **CTR** (porcentaje de clics — mejora título/descripción si es bajo)
- **Posición media** por consulta
- **Páginas indexadas** (debe ser 4: inicio, precios, contacto, FAQ)

Meta realista: primeras impresiones en 2–4 semanas; tráfico orgánico útil en 3–6 meses con constancia.

## Despliegue a producción

```bash
bash deploy/seo-deploy.sh
```

Sube rutas, config, controladores, vistas SEO y sincroniza `public/robots.txt` con el `ADMIN_PANEL_PATH` del `.env` del VPS.

Tras cambiar `ADMIN_PANEL_PATH` en producción, vuelve a ejecutar el script o actualiza manualmente `Disallow: /{ADMIN_PANEL_PATH}/` en `public/robots.txt`.

## Editar SEO en código

| Cambio | Archivo |
|--------|---------|
| Título/descripción por página | `config/seo.php` |
| Preguntas FAQ | `LandingController::faq()` |
| Nueva página pública | Ruta + entrada en `seo.pages` + sitemap automático |

Ver también: [HOSTING_DOMINIO_Y_DESPLIEGUE.md](HOSTING_DOMINIO_Y_DESPLIEGUE.md), [MODELO_NEGOCIO.md](MODELO_NEGOCIO.md).
