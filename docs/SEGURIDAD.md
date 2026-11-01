# Seguridad — panel admin y formularios públicos

## Panel super administrador

### Ruta discreta (solo producción)

Por defecto el panel usa el prefijo `/admin`, adecuado para **desarrollo local** sin configurar nada.

En **producción** configure una ruta personalizada:

```env
ADMIN_PANEL_PATH=va-cp-m8k3x7q2
ADMIN_BLOCK_LEGACY_PATH=true
```

- Login local: `http://127.0.0.1:8000/admin/login`
- Login producción: `https://tudominio.com/{ADMIN_PANEL_PATH}/login`
- Los despliegues (`deploy/vps-update.sh`) **no sobrescriben** `.env`; la ruta de producción se mantiene entre actualizaciones.

### Recuperación de clave (super admin)

Flujo **sin envío de correo** (dos pasos de verificación):

1. **Pregunta secreta** y **correo de recuperación** configurados en **Super usuarios**.
2. Ingresa usuario → completa el **correo completo** que coincide con el enmascarado (ej. `ing********.com` → `ingenio_alex@hotmail.com`).
3. Si el correo es correcto, se activa la **pregunta secreta**.
4. Tras responder bien, aparece el **enlace en pantalla** (60 min) para la nueva clave.

Rutas (bajo el mismo prefijo del panel):

| Paso | Ruta |
|------|------|
| Solicitar | `/{ADMIN_PANEL_PATH}/recuperar` |
| Pregunta + correo enmascarado | `/{ADMIN_PANEL_PATH}/recuperar/pregunta` |
| Enlace en pantalla | `/{ADMIN_PANEL_PATH}/recuperar/enlace` |
| Nueva clave | `/{ADMIN_PANEL_PATH}/recuperar/nueva-clave/{token}` |

No requiere SMTP ni Brevo para recuperación. Ver [BREVO_CORREO.md](BREVO_CORREO.md) solo si en el futuro desea correos transaccionales.

Límite: 30 peticiones de recuperación por hora por IP (`throttle:admin-recuperar`).

### Contraseñas super admin

- Mínimo **12 caracteres**: mayúsculas, minúsculas, números y símbolo (`App\Rules\ContrasenaSuperAdmin`).
- En base de datos se almacenan con **bcrypt** (`Usuario` cast `password` → `hashed`). Nunca en texto plano.
- En tránsito: use **HTTPS** (ya activo en ventasagil.com). La contraseña viaja cifrada por TLS; el servidor solo compara el hash.

### Protección del login

| Medida | Detalle |
|--------|---------|
| Rate limit | 5 intentos/minuto por IP + usuario (`throttle:admin-login`) |
| Log | Intentos fallidos en `storage/logs/laravel.log` (usuario, IP, user-agent) |
| Mensaje genérico | «Credenciales incorrectas» (no revela si el usuario existe) |
| Sesión | Regeneración de ID tras login exitoso |
| UI | Título «Acceso restringido» (sin «VentaPOS Admin» en pantalla) |

### Recomendaciones adicionales

1. Cambiar contraseña del super admin si era débil (ej. usuario `super` con clave corta).
2. `SESSION_ENCRYPT=true` en producción (cifrado de datos de sesión en BD/archivo).
3. No compartir la URL del panel por WhatsApp o correo sin cifrar.
4. Revisar periódicamente `storage/logs/laravel.log` por intentos de login.

## Formulario beta / prospectos

### ¿Por qué aparecen «prospectos» sin haber publicado?

`ventasagil.com` es un sitio **público** indexable por buscadores y escaneado por bots. El formulario en `/contacto` envía a `POST /beta`. Cualquier bot que encuentre el dominio puede rellenarlo.

Los casos **Otis**, **Jaylan** y **DavidBof** son **spam de marketing/SEO** (mensajes en inglés o español genérico sobre IA/posicionamiento), no acceso al admin ni filtración de datos internos. Origen en sistema: `Formulario beta`.

### Antispam implementado

| Control | Comportamiento |
|---------|----------------|
| Honeypot `empresa_web` | Campo oculto; si viene relleno → se simula éxito sin guardar |
| Tiempo mínimo | Menos de 4 s entre carga y envío → bot |
| Patrones en mensaje | SEO, Telegram, outreach, etc. |
| Rate limit | 8 envíos/hora por IP (`throttle:beta-form`) |

Los bots rechazados ven el mismo mensaje de agradecimiento (no confirman que fueron bloqueados).

### Gestión en admin

- Listado: `{ADMIN_PANEL_PATH}/prospectos`
- **Ver** abre el mensaje completo y permite marcar como **descartado** (spam).
- En el listado, el interruptor **Descartados** (activo por defecto) oculta los marcados como descartado; desactívalo para verlos.

## Referencias

- Variables: `.env.example` (`ADMIN_PANEL_PATH`, `ADMIN_BLOCK_LEGACY_PATH`)
- Config: `config/admin.php`
- Rutas: `routes/admin.php`, bloqueo legacy en `routes/web.php`
- Antispam: `app/Services/ProspectoAntispamService.php`
- Despliegue: [PUESTA_EN_MARCHA.md](PUESTA_EN_MARCHA.md)
