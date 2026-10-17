# 🎓 Módulo SeccionK - Promoción 2001 Sección K

Sistema de gestión y seguimiento de cuotas mensuales y eventos para la Promoción 2001 - Sección K.

**Acceso:** `ventasagil.com/seccionk`

---

## 🚀 Características

### 🔍 Módulo Público
- Búsqueda por DNI + primer apellido
- Perfil de miembro con:
  - Timeline de cuotas mensuales (Oct 2026 - Dic 2027)
  - Lista de eventos con estado de participación
  - Historial de pagos
  - Estadísticas de participación
- Registro de nuevos miembros

### 👤 Panel Administrativo
- Dashboard con métricas y estadísticas
- Gestión completa de miembros (CRUD)
- Gestión de eventos deportivos/sociales
- Asignación de participantes a eventos
- Registro de aportes con:
  - Monto
  - Número de transacción
  - Imagen de comprobante (opcional)
- Sistema de autenticación independiente

---

## 📂 Estructura

```
app/
├── Http/
│   ├── Controllers/SeccionK/
│   │   ├── SeccionKPublicoController.php
│   │   ├── SeccionKAuthController.php
│   │   └── Admin/
│   │       ├── AdminDashboardController.php
│   │       ├── AdminPromocionController.php
│   │       └── AdminEventoController.php
│   └── Middleware/
│       └── SeccionKAuth.php
└── Models/
    ├── SeccionKPromocion.php
    ├── SeccionKEvento.php
    ├── SeccionKParticipacion.php
    └── SeccionKUsuario.php

database/
├── migrations/
│   └── 2026_10_11_000001_crear_tablas_seccionk.php
└── seeders/
    └── SeccionKSeeder.php

resources/views/seccionk/
├── layout.blade.php
├── index.blade.php
├── perfil.blade.php
├── registro.blade.php
└── admin/
    ├── layout.blade.php
    ├── login.blade.php
    ├── dashboard.blade.php
    ├── promociones/
    └── eventos/

routes/
└── seccionk.php

config/
└── auth.php (guard 'seccionk')

bootstrap/
└── app.php (registro de rutas)
```

---

## 🗂️ Base de Datos

### Tablas (Independientes de VentaPOS)

#### `seccionk_promociones`
Miembros de la promoción.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID único |
| nombre | string | Nombre completo |
| apellido_paterno | string | Apellido paterno |
| apellido_materno | string | Apellido materno |
| dni | string(8) | DNI único |
| direccion | text | Dirección |
| celular | string(9) | Celular |
| participa_cuota | boolean | Si participa en cuotas |
| referencia | text | Referencias |
| visible | boolean | Si está visible |
| created_at, updated_at | timestamps | Fechas |

#### `seccionk_eventos`
Eventos deportivos y sociales.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID único |
| tipo | enum | 'cuota_mensual', 'futbol', 'voley', 'otro' |
| descripcion | string | Descripción del evento |
| monto | decimal(10,2) | Monto del evento |
| fecha | date | Fecha del evento |
| periodo_mes | string | Mes (ej: 2026-10) |
| created_at, updated_at | timestamps | Fechas |

#### `seccionk_participaciones`
Participación y pagos de miembros en eventos.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID único |
| seccionk_promocion_id | bigint | FK a promociones |
| seccionk_evento_id | bigint | FK a eventos |
| participo | boolean | Si participó |
| pago | boolean | Si pagó |
| monto_pagado | decimal(10,2) | Monto pagado |
| numero_transaccion | string | Número de transacción |
| comprobante_path | string | Ruta de imagen |
| fecha_pago | date | Fecha de pago |
| created_at, updated_at | timestamps | Fechas |

#### `seccionk_usuarios`
Usuarios administradores del módulo.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID único |
| username | string | Usuario único |
| password | string | Contraseña hasheada |
| nombre | string | Nombre completo |
| email | string | Email |
| activo | boolean | Si está activo |
| created_at, updated_at | timestamps | Fechas |

---

## 🌐 Rutas

### Públicas
```
GET  /seccionk                      # Búsqueda
POST /seccionk/buscar               # Procesar búsqueda
GET  /seccionk/perfil/{id}          # Perfil de miembro
GET  /seccionk/registro             # Formulario de registro
POST /seccionk/registro             # Guardar registro
```

### Autenticación Admin
```
GET  /seccionk/admin/login          # Login
POST /seccionk/admin/login          # Procesar login
POST /seccionk/admin/logout         # Cerrar sesión
```

### Admin (protegidas con `seccionk.auth`)
```
GET  /seccionk/admin                          # Dashboard
GET  /seccionk/admin/promociones              # Listar miembros
GET  /seccionk/admin/promociones/create       # Crear miembro
POST /seccionk/admin/promociones              # Guardar miembro
GET  /seccionk/admin/promociones/{id}         # Ver miembro
GET  /seccionk/admin/promociones/{id}/edit    # Editar miembro
PUT  /seccionk/admin/promociones/{id}         # Actualizar miembro
DELETE /seccionk/admin/promociones/{id}       # Eliminar (ocultar) miembro

GET  /seccionk/admin/eventos                  # Listar eventos
GET  /seccionk/admin/eventos/create           # Crear evento
POST /seccionk/admin/eventos                  # Guardar evento
GET  /seccionk/admin/eventos/{id}             # Ver evento
GET  /seccionk/admin/eventos/{id}/edit        # Editar evento
PUT  /seccionk/admin/eventos/{id}             # Actualizar evento
DELETE /seccionk/admin/eventos/{id}           # Eliminar evento
GET  /seccionk/admin/eventos/{id}/participantes # Gestionar participantes
POST /seccionk/admin/eventos/{id}/participantes # Guardar participantes
```

---

## 🔐 Autenticación

### Guard Independiente
```php
// config/auth.php
'guards' => [
    'seccionk' => [
        'driver' => 'session',
        'provider' => 'seccionk',
    ],
],

'providers' => [
    'seccionk' => [
        'driver' => 'eloquent',
        'model' => App\Models\SeccionKUsuario::class,
    ],
],
```

### Uso
```php
// Login
Auth::guard('seccionk')->attempt($credentials);

// Verificar autenticación
Auth::guard('seccionk')->check();

// Obtener usuario
Auth::guard('seccionk')->user();

// Logout
Auth::guard('seccionk')->logout();
```

### Middleware
```php
// routes/seccionk.php
Route::middleware('seccionk.auth')->group(function () {
    // Rutas protegidas
});
```

---

## 🎨 Frontend

### Tecnologías
- **CSS Framework:** Tailwind CSS 4.0 (CDN)
- **Iconos:** Font Awesome 6.4
- **Fuentes:** Inter (Google Fonts)
- **Componentes:** Blade templates

### Tema
- **Colores principales:** Gradiente púrpura (#667eea → #764ba2)
- **Estilo:** Moderno, limpio, profesional
- **Responsive:** Totalmente adaptable

### Componentes Destacados

**Timeline de Cuotas**
```blade
<div class="timeline-item relative pl-12 pb-8">
    <div class="absolute left-0 top-0 w-10 h-10 rounded-full flex items-center justify-center">
        <i class="fas fa-check"></i>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <!-- Contenido -->
    </div>
</div>
```

**Cards con Hover**
```blade
<div class="bg-white rounded-xl shadow-lg p-6 card-hover">
    <!-- Contenido -->
</div>
```

---

## 🚀 Despliegue

### Script Automatizado

```bash
curl -o deploy.sh https://raw.githubusercontent.com/ingenioalex/VentaPOS/cursor/seccionk-module-0c7c/deploy-vps-ruta.sh
chmod +x deploy.sh
./deploy.sh
```

### Manual

Ver [`docs/DEPLOY_VPS_RUTA.md`](../docs/DEPLOY_VPS_RUTA.md)

---

## 🧪 Datos de Prueba

El seeder carga:

**Admin**
- Usuario: `admin`
- Contraseña: `seccionk2026`

**Miembros**
- Juan García (DNI: 12345678)
- María López (DNI: 87654321)
- Carlos Rodríguez (DNI: 11223344)

**Eventos**
- 15 cuotas mensuales (Oct 2026 - Dic 2027) @ S/. 50.00
- Fulbito Relámpago (21 Sep 2026) @ S/. 5.00

---

## 📊 Estadísticas del Dashboard

- Total de miembros activos
- Recaudación mensual
- Tasa de participación
- Próximos eventos

---

## 🔧 Comandos Útiles

```bash
# Ejecutar migraciones
php artisan migrate

# Cargar datos de prueba
php artisan db:seed --class=SeccionKSeeder

# Listar rutas
php artisan route:list --path=seccionk

# Limpiar caché
php artisan route:clear
php artisan config:clear
php artisan view:clear
```

---

## 📝 Notas de Desarrollo

### Separación de VentaPOS
- Base de datos completamente independiente
- Guard de autenticación propio
- Rutas con prefijo `/seccionk`
- Sin conflictos con sistema principal

### Extensibilidad
- Fácil agregar nuevos tipos de eventos
- Sistema de permisos ampliable
- API REST lista para implementar
- Exportación de reportes preparada

---

## 🔄 Próximas Mejoras

- [ ] Exportar a PDF/Excel
- [ ] Notificaciones por email
- [ ] Dashboard con gráficos Chart.js
- [ ] Pagos online integrados
- [ ] App móvil (API REST)
- [ ] Sistema de roles granular

---

## 📄 Licencia

MIT License - Ver LICENSE en el repositorio principal.

---

## 👥 Créditos

Desarrollado para la **Promoción 2001 - Sección K**

---

**Documentación actualizada:** Octubre 2026  
**Versión:** 1.0  
**Estado:** ✅ Producción
