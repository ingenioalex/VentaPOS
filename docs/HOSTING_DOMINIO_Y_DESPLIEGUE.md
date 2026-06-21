# VentaPOS — Hosting de pago + local en paralelo

**Estrategia:** desarrollar en Windows (Laragon) y tener **producción en hosting de pago** desde la semana 1–2. Dominio propio cuando el producto esté presentable.

---

## Por qué hosting de pago (no gratis)

| Gratis (Miarroba free, 000webhost…) | De pago (VPS / cloud) |
|-------------------------------------|------------------------|
| Se cae, lento, publicidad | Recursos dedicados |
| Sin SSL fiable / límites estrictos | SSL, backups, uptime 99.9% |
| PHP viejo, sin Composer/SSH | PHP 8.2, SSH, cron, colas |
| No sirve para SaaS multi-empresa | Escala con clientes |
| Mala imagen ante clientes | Profesional para vender online |

**Para vender por internet:** hosting de pago es obligatorio, no lujo.

---

## Recomendación de hosting (2026)

### Opción A — VPS (recomendada para VentaPOS SaaS)

Control total. Un solo servidor para landing + app + BD.

| Proveedor | Plan | Precio aprox. | Notas |
|-----------|------|---------------|-------|
| **Hostinger VPS** | KVM 1 | ~S/25–40/mes | Panel fácil, Perú/LATAM |
| **DigitalOcean** | Droplet 1GB | ~$6/mes (~S/22) | Muy estable, documentación |
| **Contabo** | VPS S | ~€5/mes (~S/20) | Barato, buen disco |
| **Hetzner** | CX22 | ~€4/mes | Excelente precio/Europa |

**Specs mínimas:** 1 vCPU, 2 GB RAM, 40 GB SSD, Ubuntu 22.04

**Specs recomendadas (10–30 clientes):** 2 vCPU, 4 GB RAM

### Opción B — Cloud managed Laravel (más caro, menos trabajo)

| Proveedor | Precio | Para cuándo |
|-----------|--------|-------------|
| Laravel Forge + DO | ~$15–25/mes total | Si quieres deploy automático |
| Ploi + VPS | Similar | Panel deploy Git |

### No recomendado para VentaPOS

- Hosting compartido barato sin SSH
- Planes "ilimitados" S/5/mes
- Render/Railway free tier (límites, duerme)

---

## Dominios — estrategia en 2 fases

### Fase 1 — Usar dominio del hosting (semana 1–4)

Mientras desarrollas, usa el subdominio que da el VPS o un dominio barato temporal:

```
https://srv123.hostinger.com        ← solo pruebas internas
https://ventapos-demo.tudominio-temporal.xyz
```

O compra **ya** un dominio barato `.com` (~S/40/año) como placeholder.

### Fase 2 — Dominio final comercial (semana 4–8)

Cuando landing + demo POS estén listos, compras el dominio **definitivo** y apuntas DNS.

**Criterios para un dominio que vende:**
- Corto, fácil de decir por teléfono
- Relacionado con ventas/POS/negocio
- `.pe` genera confianza en Perú
- Sin guiones ni números

### Ideas de dominio (verificar disponibilidad)

| Dominio | Por qué |
|---------|---------|
| **ventapos.pe** | Directo, marca = producto |
| **mipunto.pe** | Cercano al usuario ("mi punto de venta") |
| **vendemas.pe** | Beneficio claro |
| **cajapos.pe** | Corto, memorable |
| **negociodigital.pe** | Más amplio |
| **stockya.pe** | Inventario + urgencia |
| **puntoventa.pe** | SEO fuerte en Perú |

**Recomendación:** `ventapos.pe` o `mipunto.pe` — cortos, claros, profesionales.

**Registradores Perú:** NIC.pe, PuntoPE, Hostinger, Namecheap (.com backup).

---

## Arquitectura en el servidor

```
ventapos.pe                    → Landing (marketing)
www.ventapos.pe                → Redirige a raíz
app.ventapos.pe                → Aplicación Laravel (login)
bodega-juan.ventapos.pe        → Empresa cliente (wildcard)
```

**DNS necesario:**
```
A     @      → IP del VPS
A     app    → IP del VPS
A     *      → IP del VPS   (wildcard subdominios)
```

**Stack en VPS:**
- Ubuntu 22.04
- Nginx
- PHP 8.2 + extensions (mbstring, xml, curl, mysql, zip, bcmath)
- MySQL 8
- Redis (opcional fase 2)
- Certbot (SSL Let's Encrypt — gratis pero en servidor de pago)
- Cloudflare (gratis) delante para CDN + protección DDoS

---

## Trabajo en paralelo: local + producción

```
┌─────────────────────┐         git push          ┌─────────────────────┐
│  LOCAL (Windows)    │ ────────────────────────► │  VPS (producción)   │
│  Laragon            │         deploy            │  Nginx + PHP 8.2    │
│  localhost:8000     │                           │  app.ventapos.pe    │
│  BD: ventapos_local │                           │  BD: ventapos_prod  │
└─────────────────────┘                           └─────────────────────┘
         │                                                  │
         └──────────── misma rama main en Git ────────────────┘
```

### Flujo diario

| Paso | Dónde | Acción |
|------|-------|--------|
| 1 | Local | Programar con Cursor + `php artisan serve` |
| 2 | Local | Probar cambios |
| 3 | Git | `git commit` + `git push` |
| 4 | VPS | `git pull` + `php artisan migrate` + cache clear |
| 5 | Producción | Verificar en URL del hosting |

### Variables de entorno

| Variable | Local `.env` | Producción `.env` |
|----------|--------------|-------------------|
| `APP_URL` | `http://localhost:8000` | `https://app.ventapos.pe` |
| `DB_DATABASE` | `ventapos_local` | `ventapos_prod` |
| `APP_DEBUG` | `true` | **`false`** |
| `APP_ENV` | `local` | **`production`** |

**Nunca** subir `.env` a Git. Cada entorno tiene el suyo.

---

## Presupuesto mensual realista

| Concepto | Mes 1–3 | Cuando crezcas |
|----------|---------|----------------|
| VPS Hostinger/DO | S/25–45 | S/60–90 (más RAM) |
| Dominio `.pe` | S/7/mes (~S/85/año) | Igual |
| Backups VPS | incluido o S/10 | S/15 |
| Cloudflare | S/0 | S/0 |
| **Total** | **~S/35–55/mes** | **~S/75–110/mes** |

Dominio se paga 1 vez al año; el VPS es el costo fijo principal.

---

## Plan de acción (orden)

### Semana 1
- [ ] Instalar Laragon local
- [ ] Contratar VPS (Hostinger o DigitalOcean)
- [ ] Instalar Ubuntu + Nginx + PHP 8.2 + MySQL en VPS
- [ ] Crear repo Git (GitHub privado)
- [ ] Deploy Laravel vacío → URL temporal del VPS

### Semana 2
- [ ] Landing en `/` del dominio temporal
- [ ] App en subdominio `app.` o `/app`
- [ ] SSL activo (HTTPS)
- [ ] Formulario prospectos funcionando

### Semana 3–4
- [ ] POS MVP en producción (beta cerrada)
- [ ] Comprar dominio definitivo (`ventapos.pe` o elegido)
- [ ] Apuntar DNS al mismo VPS (no migrar servidor, solo DNS)

### Mes 2+
- [ ] Cloudflare delante
- [ ] Backups automáticos diarios
- [ ] Monitoreo uptime (UptimeRobot gratis)

---

## Checklist antes de ofrecer por internet

- [ ] HTTPS activo
- [ ] `APP_DEBUG=false` en producción
- [ ] Contraseñas BD fuertes
- [ ] Backups automáticos configurados
- [ ] Landing con precios y WhatsApp
- [ ] Demo o video del POS
- [ ] Política de privacidad básica
- [ ] Dominio `.pe` (confianza local)

---

## Comandos deploy rápido (referencia)

```bash
# En el VPS, después de git pull:
cd /var/www/ventapos
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl reload php8.2-fpm
```

---

## Resumen

| Pregunta | Respuesta |
|----------|-----------|
| ¿Gratis o de pago? | **De pago** (VPS ~S/30/mes) |
| ¿Local y hosting a la par? | **Sí** — local desarrolla, VPS muestra avance |
| ¿Dominio al inicio? | Subdominio VPS primero; dominio final en semana 4 |
| ¿Qué dominio? | `ventapos.pe` o `mipunto.pe` (corto, vende) |
| ¿Mejor hosting? | **VPS** Hostinger o DigitalOcean para Laravel SaaS |
