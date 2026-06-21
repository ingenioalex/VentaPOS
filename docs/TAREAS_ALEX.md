# VentaPOS — Qué debes hacer TÚ (mientras se construye)

Actualizado: 6 jun 2026

Divide tu tiempo: **40% negocio / 60% dejar que Cursor construya** (o 50/50 si instalas entorno hoy).

---

## HOY (día 1) — Bloqueantes técnicos

### 1. Instalar entorno moderno (URGENTE)

Tu PC tiene PHP 7.3 — Laravel 11 **no funciona**.

**Opción recomendada: Laragon**
1. Descarga: https://laragon.org/download (Full)
2. Instala → abre Laragon → "Start All"
3. Verifica en terminal Laragon:
   ```bash
   php -v        # debe decir 8.2 o 8.3
   composer -V   # debe responder versión
   ```
4. Avísame cuando esté listo → ejecutamos `composer install` y arrancamos Laravel.

**Tiempo:** 30–45 minutos

---

## HOY (día 1) — Negocio en paralelo

### 2. Contratar VPS de pago (en paralelo con Laragon)

- **Recomendado:** Hostinger VPS o DigitalOcean Droplet
- Specs: 2 GB RAM, Ubuntu 22.04, ~**S/25–45/mes**
- Usa la URL temporal del VPS para demo (antes del dominio final)
- Guía completa: `docs/HOSTING_DOMINIO_Y_DESPLIEGUE.md`

**Tiempo:** 30–45 minutos

### 3. Dominio (puede esperar a semana 3–4)

- Busca: **ventapos.pe**, **mipunto.pe**, **cajapos.pe**
- Al inicio basta la URL del VPS; dominio final cuando landing esté lista
- Costo: ~S/70–90/año (.pe)

**Tiempo:** 15 minutos (cuando decidas marca)

### 3. Crear WhatsApp Business

- Número dedicado o el tuyo con perfil profesional
- Nombre: VentaPOS
- Descripción: "Sistema de ventas e inventario para bodegas y negocios en Chiclayo"
- Mensaje de bienvenida:
  > "Hola, gracias por contactar VentaPOS. Te ayudamos a controlar ventas e inventario desde el celular. ¿Qué tipo de negocio tienes?"

**Tiempo:** 20 minutos

### 4. Lista de 10 negocios para visitar (semana 2–3)

Llena esta tabla:

| # | Nombre negocio | Rubro | Dirección/zona | Contacto | ¿Conocido? |
|---|----------------|-------|----------------|----------|------------|
| 1 | | Bodega | | | |
| 2 | | Ferretería | | | |
| 3 | | Farmacia | | | |
| 4 | | Bodega | | | |
| 5 | | | | | |
| 6 | | | | | |
| 7 | | | | | |
| 8 | | | | | |
| 9 | | | | | |
| 10 | | | | | |

**Prioriza:** negocios donde conozcas al dueño.

---

## ESTA SEMANA — Preparar beta

### 5. Confirmar 3 candidatos beta

Necesitas (idealmente):
- 1 **bodega**
- 1 **ferretería** o miscelánea
- 1 **farmacia** o botica

**Oferta beta:**
> "Gratis 2 meses. A cambio: usas el sistema a diario, me das feedback semanal y permiso para poner tu logo en la web."

Anota aquí:

| # | Negocio | Rubro | WhatsApp | ¿Aceptó beta? |
|---|---------|-------|----------|---------------|
| 1 | | | | ☐ |
| 2 | | | | ☐ |
| 3 | | | | ☐ |

### 6. Proyecto Python encontrado ✓

**Ruta:** `D:\Proyectos\rms_desktop\`

**Tu tarea ahora:**
- [ ] Si tienes dump de PostgreSQL de Kali, cópialo a `D:\Proyectos\VentaPOS\legacy\`
- [ ] Confirma si `rms_db` tiene datos de productos reales o solo estructura
- [ ] No subas `rms_desktop` a GitHub público (tiene contraseña en `db_client.py`)

**Opcional — exportar desde Kali:**
```bash
pg_dump -U rms_user -d rms_db > rms_backup.sql
```

---

## SEMANA 2 — Comercial

| Tarea | Meta |
|-------|------|
| Visitar 5 negocios de tu lista | Mostrar demo en celular |
| Publicar 1 post Facebook/WhatsApp status | "Busco 3 negocios para probar sistema gratis" |
| Pedir fotos de cuaderno actual | Para marketing "antes/después" |

---

## SEMANA 3–4 — Validación

| Tarea | Detalle |
|-------|---------|
| Recoger lista productos de beta | Excel o foto del cuaderno |
| Anotar cómo cada rubro registra | Farmacia ¿usa lote? Ferretería ¿por metro? |
| Enviar feedback semanal a Cursor | "La farmacia necesita campo vencimiento" |

---

## NO hagas esto todavía

- ❌ Contratar publicidad Facebook Ads
- ❌ Comprar VPS caro
- ❌ Prometer facturación SUNAT
- ❌ Registrar empresa formal (hasta tener 3 clientes pagando, salvo que ya la tengas)
- ❌ Construir otra app en paralelo

---

## Checklist rápido diario

```
☐ ¿Respondí WhatsApp de leads en < 2 horas?
☐ ¿Anoté 1 problema que vi en un negocio local?
☐ ¿Guardé comprobante de gastos del proyecto?
☐ ¿Laragon/PHP 8.2 funciona? (solo semana 1)
```

---

## Cuando Laragon esté listo, escríbeme:

> "Laragon listo, PHP 8.x y Composer funcionan"

Y ejecutamos juntos:
```bash
cd d:\Proyectos\VentaPOS
composer create-project laravel/laravel . --prefer-dist
php artisan migrate
php artisan serve
```
