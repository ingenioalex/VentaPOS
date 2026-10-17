# Módulo Sección K - Promoción 2001

## 📋 Descripción

Módulo independiente para gestionar la Promoción 2001 - Sección K, con seguimiento de eventos, cuotas mensuales y participaciones.

**Subdominio:** `seccionk.ventasagil.com`

---

## ✨ Características Implementadas

### 🌐 Portal Público

#### 1. Página de Consulta (Inicio)
- Búsqueda por DNI y primer apellido
- Diseño moderno con gradientes purple/indigo
- Responsivo y profesional

#### 2. Perfil del Miembro
- **Información personal**: DNI, nombre completo, celular
- **Estadísticas destacadas**:
  - Cuotas pagadas vs total
  - Total aportado en cuotas
  - Número de eventos
  - Participaciones confirmadas

- **Línea de Tiempo de Cuotas Mensuales**:
  - Visualización desde Octubre 2026 hasta Diciembre 2027 (15 meses)
  - Estados: Pagado (verde) / Pendiente (amarillo)
  - Iconos visuales con check/reloj
  - Muestra monto aportado y fecha de pago
  - Línea de tiempo vertical con gradiente

- **Tarjetas de Eventos**:
  - Lista de eventos especiales
  - Indicadores de participación
  - Montos aportados
  - Fechas de realización

#### 3. Registro de Nuevos Miembros
- Formulario completo con validación
- Campos:
  - DNI (8 dígitos, único)
  - Nombres
  - Apellido paterno y materno
  - Dirección (opcional)
  - Celular (opcional)
  - Referencias de otras promociones (opcional)
  - Checkbox para participar en cuotas mensuales

### 🔐 Panel de Administración

#### 1. Autenticación
- Login independiente con guard personalizado
- Usuario por defecto: `admin` / `seccionk2026`
- Sesión persistente con "recordarme"

#### 2. Dashboard
- Estadísticas generales:
  - Total de promociones
  - Promociones que participan en cuotas
  - Total de eventos
  - Cuotas mensuales creadas
- Eventos recientes
- Accesos rápidos a funciones principales

#### 3. Gestión de Promociones
- **Listar**:
  - Tabla con todos los miembros
  - Filtros por búsqueda, participación en cuotas, activos/inactivos
  - Paginación
- **Crear**: Formulario completo
- **Editar**: Modificar datos del miembro
- **Ocultar**: Marcar como inactivo (soft delete)

#### 4. Gestión de Eventos
- **Listar**:
  - Todos los eventos y cuotas mensuales
  - Filtros por tipo (evento/cuota)
  - Ver activos/inactivos
- **Crear**:
  - Eventos especiales
  - Cuotas mensuales
  - Campos: nombre, descripción, fecha, monto, tipo
- **Editar**: Modificar evento
- **Gestionar Participantes**:
  - Seleccionar miembros que participan
  - Registrar pagos con:
    - Checkbox de participación
    - Checkbox de pago
    - Monto aportado
    - Número de transacción (opcional)
    - Subir imagen de sustento (opcional)
    - Fecha de pago
    - Observaciones

---

## 🗄️ Estructura de Base de Datos

### Tabla: `seccionk_promociones`
Miembros de la promoción.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | PK |
| dni | varchar(8) | Único, índice |
| nombre | varchar(100) | Nombres |
| apellido_paterno | varchar(100) | Índice |
| apellido_materno | varchar(100) | |
| direccion | varchar(255) | Nullable |
| celular | varchar(15) | Nullable |
| participa_cuotas | boolean | Default: false |
| referencias | text | Nullable, otras promociones |
| activo | boolean | Default: true, índice |
| timestamps | | created_at, updated_at |

### Tabla: `seccionk_eventos`
Eventos y cuotas mensuales.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | PK |
| nombre | varchar(200) | |
| descripcion | text | Nullable |
| fecha | date | Índice |
| monto | decimal(10,2) | Sugerido |
| tipo | enum | 'evento', 'cuota_mensual', índice |
| mes_cuota | varchar(20) | Nullable, ej: "Octubre 2026" |
| activo | boolean | Default: true, índice |
| timestamps | | |

### Tabla: `seccionk_participaciones`
Relación promoción-evento (participación y pago).

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | PK |
| promocion_id | bigint | FK, índice único con evento_id |
| evento_id | bigint | FK |
| participo | boolean | Default: false, índice |
| pago | boolean | Default: false, índice |
| monto_aportado | decimal(10,2) | Default: 0 |
| numero_transaccion | varchar(100) | Nullable |
| imagen_sustento | varchar(255) | Nullable, ruta storage |
| fecha_pago | date | Nullable, índice |
| observaciones | text | Nullable |
| timestamps | | |

### Tabla: `seccionk_usuarios`
Usuarios del panel admin.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | PK |
| usuario | varchar(60) | Único |
| password | varchar | Hash |
| nombre | varchar(100) | |
| activo | boolean | Default: true |
| remember_token | varchar | |
| timestamps | | |

---

## 🎨 Diseño UI/UX

### Paleta de Colores
- **Principal**: Gradiente purple (#667eea) a indigo (#764ba2)
- **Secundarios**: 
  - Verde para pagado/activo (#10b981)
  - Amarillo para pendiente (#f59e0b)
  - Rojo para errores (#ef4444)
  - Azul para información (#3b82f6)

### Componentes Visuales
- **Cards con hover**: Elevación y transformación
- **Iconos Font Awesome 6.4**: Consistentes en todo el sistema
- **Gradientes**: Backgrounds y botones
- **Timeline**: Línea vertical con gradiente y círculos de estado
- **Badges**: Redondeados con colores semánticos
- **Forms**: Border-2 con focus ring purple

### Tipografía
- **Font**: Inter (Google Fonts)
- **Pesos**: 300, 400, 500, 600, 700, 800

---

## 🛣️ Rutas

### Públicas
```
GET  /                          - Página de consulta
POST /buscar                    - Buscar promoción por DNI+apellido
GET  /perfil/{id}              - Ver perfil con línea de tiempo
GET  /registro                  - Formulario de registro
POST /registro                  - Guardar nuevo miembro
```

### Admin (requiere autenticación)
```
GET  /admin/login              - Formulario login
POST /admin/login              - Procesar login
POST /admin/logout             - Cerrar sesión

GET  /admin                    - Dashboard
GET  /admin/promociones        - Listar promociones
GET  /admin/promociones/create - Crear promoción
POST /admin/promociones        - Guardar promoción
GET  /admin/promociones/{id}/edit - Editar promoción
PUT  /admin/promociones/{id}   - Actualizar promoción
DELETE /admin/promociones/{id} - Ocultar promoción

GET  /admin/eventos            - Listar eventos
GET  /admin/eventos/create     - Crear evento
POST /admin/eventos            - Guardar evento
GET  /admin/eventos/{id}/edit  - Editar evento
PUT  /admin/eventos/{id}       - Actualizar evento
DELETE /admin/eventos/{id}     - Ocultar evento
GET  /admin/eventos/{id}/participantes - Gestionar participantes
POST /admin/eventos/{id}/participantes - Guardar participaciones
```

---

## 🚀 Instalación y Configuración

### 1. Ejecutar Migraciones
```bash
php artisan migrate
```

Esto crea las 4 tablas necesarias:
- `seccionk_promociones`
- `seccionk_eventos`
- `seccionk_participaciones`
- `seccionk_usuarios`

### 2. Sembrar Datos Iniciales
```bash
php artisan db:seed --class=SeccionKSeeder
```

Esto crea:
- Usuario admin: `admin` / `seccionk2026`
- 15 cuotas mensuales (Octubre 2026 - Diciembre 2027)
- 3 promociones de ejemplo
- 1 evento de ejemplo (Partido de Fútbol)

### 3. Configurar Subdominio

#### Desarrollo Local
En `hosts` (Windows: `C:\Windows\System32\drivers\etc\hosts`):
```
127.0.0.1   seccionk.ventasagil.com
```

#### Servidor de Producción
**DNS A Record:**
```
Host: seccionk
Type: A
Value: [IP del servidor]
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name seccionk.ventasagil.com;
    root /var/www/ventasagil/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

**Apache (VirtualHost):**
```apache
<VirtualHost *:80>
    ServerName seccionk.ventasagil.com
    DocumentRoot /var/www/ventasagil/public

    <Directory /var/www/ventasagil/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 4. Permisos de Storage
```bash
chmod -R 775 storage/app/public
php artisan storage:link
```

Para imágenes de sustento de pagos.

---

## 🧪 Pruebas

### Flujo de Prueba Completo

#### 1. Portal Público
1. Acceder a `http://seccionk.ventasagil.com`
2. Buscar con: DNI `12345678` y apellido `García`
3. Verificar perfil con línea de tiempo de cuotas
4. Ver eventos y participaciones

#### 2. Registro
1. Click en "Registrarse"
2. Llenar formulario con datos de prueba
3. Marcar "Deseo participar en cuotas mensuales"
4. Verificar redirección a perfil

#### 3. Panel Admin
1. Acceder a `http://seccionk.ventasagil.com/admin/login`
2. Login: `admin` / `seccionk2026`
3. Ver dashboard con estadísticas
4. Listar promociones y eventos
5. Crear nuevo evento
6. Gestionar participantes del evento
7. Marcar pagos con monto y número de transacción

---

## 📱 Características Responsivas

- **Mobile First**: Diseño adaptado para celulares
- **Breakpoints**:
  - sm: 640px
  - md: 768px
  - lg: 1024px
  - xl: 1280px

- **Elementos adaptativos**:
  - Grid de estadísticas: 1 columna → 2 → 4
  - Timeline: ancho completo en mobile
  - Sidebar admin: oculto en mobile (puede agregarse toggle)
  - Forms: columnas apiladas en mobile

---

## 🔒 Seguridad

- **Autenticación separada**: Guard `seccionk` independiente de VentaPOS
- **Validaciones**: Todos los formularios con validación server-side
- **Sanitización**: Laravel automático en inputs
- **CSRF**: Tokens en todos los formularios POST
- **Soft Delete**: Ocultación en lugar de eliminación
- **Unique constraints**: DNI único por promoción
- **Password hashing**: Bcrypt automático

---

## 📊 Modelo de Datos - Relaciones

```
SeccionKPromocion (1) ←→ (N) SeccionKParticipacion
SeccionKEvento (1) ←→ (N) SeccionKParticipacion

SeccionKPromocion:
  - hasMany(SeccionKParticipacion)

SeccionKEvento:
  - hasMany(SeccionKParticipacion)

SeccionKParticipacion:
  - belongsTo(SeccionKPromocion)
  - belongsTo(SeccionKEvento)
```

---

## 🎯 Casos de Uso

### Caso 1: Miembro consulta sus pagos
1. Ingresa DNI y apellido
2. Sistema busca promoción activa
3. Si existe, redirige a perfil
4. Muestra línea de tiempo con todos los meses
5. Marca en verde los pagados, en amarillo los pendientes

### Caso 2: Miembro se registra
1. Click en "Registrarse"
2. Completa formulario
3. Sistema valida DNI único
4. Guarda en BD
5. Redirige a perfil con mensaje de bienvenida

### Caso 3: Admin crea evento
1. Login en panel admin
2. Eventos → Nuevo Evento
3. Completa: nombre, fecha, monto, tipo
4. Guarda evento

### Caso 4: Admin registra participación en evento
1. Eventos → Ver participantes del evento
2. Selecciona miembros que participaron
3. Marca checkboxes de participación y pago
4. Ingresa montos aportados
5. Opcionalmente: número de transacción o sube imagen
6. Guarda participaciones

### Caso 5: Admin registra cuota mensual pagada
1. Eventos → Filtrar por "Cuota Mensual"
2. Selecciona mes (ej: "Octubre 2026")
3. Gestionar participantes
4. Marca pagos con montos
5. Fecha de pago automática o manual

---

## 🔄 Flujo de Datos

```
Usuario Público
    ↓
 Consulta DNI + Apellido
    ↓
SeccionKPublicoController::buscar()
    ↓
Busca en BD: SeccionKPromocion
    ↓
Si existe → SeccionKPublicoController::perfil()
    ↓
Carga promoción + participaciones + eventos
    ↓
Vista: seccionk.perfil
    ↓
Renderiza línea de tiempo y eventos
```

```
Admin
    ↓
Login → Auth::guard('seccionk')
    ↓
Dashboard → AdminDashboardController
    ↓
CRUD Promociones → AdminPromocionController
CRUD Eventos → AdminEventoController
    ↓
Gestionar Participantes:
    - AdminEventoController::participantes()
    - Muestra evento + lista de promociones
    - Carga participaciones existentes
    ↓
Admin marca checks y montos
    ↓
AdminEventoController::guardarParticipantes()
    ↓
Transaction: actualiza/crea SeccionKParticipacion
    ↓
Sube imagen si hay archivo
```

---

## 📦 Archivos Creados

### Modelos (4)
- `app/Models/SeccionKPromocion.php`
- `app/Models/SeccionKEvento.php`
- `app/Models/SeccionKParticipacion.php`
- `app/Models/SeccionKUsuario.php`

### Controladores (5)
- `app/Http/Controllers/SeccionK/SeccionKPublicoController.php`
- `app/Http/Controllers/SeccionK/SeccionKAuthController.php`
- `app/Http/Controllers/SeccionK/Admin/AdminDashboardController.php`
- `app/Http/Controllers/SeccionK/Admin/AdminPromocionController.php`
- `app/Http/Controllers/SeccionK/Admin/AdminEventoController.php`

### Middleware (1)
- `app/Http/Middleware/SeccionKAuth.php`

### Migraciones (1)
- `database/migrations/2026_10_11_000001_crear_tablas_seccionk.php`

### Seeders (1)
- `database/seeders/SeccionKSeeder.php`

### Vistas Públicas (4)
- `resources/views/seccionk/layout.blade.php`
- `resources/views/seccionk/index.blade.php`
- `resources/views/seccionk/perfil.blade.php`
- `resources/views/seccionk/registro.blade.php`

### Vistas Admin (4+)
- `resources/views/seccionk/admin/layout.blade.php`
- `resources/views/seccionk/admin/login.blade.php`
- `resources/views/seccionk/admin/dashboard.blade.php`
- `resources/views/seccionk/admin/promociones/index.blade.php`
- *Pendientes: forms de crear/editar promociones y eventos*

### Rutas (1)
- `routes/seccionk.php`

### Configuración Modificada (2)
- `config/auth.php` - Guard y provider `seccionk`
- `bootstrap/app.php` - Rutas con dominio y middleware alias

---

## 🎨 Capturas de Funcionalidad

### Portal Público - Consulta
- Hero section con logo circular purple
- Formulario de búsqueda centrado
- Cards informativas
- Footer completo

### Perfil - Línea de Tiempo
- Header con avatar y estadísticas
- 4 tarjetas de métricas con iconos
- Timeline vertical con gradiente
- Cada mes con estado visual (check/reloj)
- Sidebar de eventos

### Admin - Dashboard
- Sidebar oscuro con navegación
- Top bar con nombre de usuario
- 4 cards de estadísticas
- Eventos recientes y accesos rápidos

### Admin - Gestión
- Tablas con paginación
- Filtros y búsqueda
- Botones de acción (editar, ocultar)
- Forms con validación visual

---

## 🚧 Pendiente de Implementar

Las siguientes vistas están pendientes (formularios estándar):

1. `resources/views/seccionk/admin/promociones/form.blade.php`
2. `resources/views/seccionk/admin/eventos/index.blade.php`
3. `resources/views/seccionk/admin/eventos/form.blade.php`
4. `resources/views/seccionk/admin/eventos/participantes.blade.php`

**Estructura recomendada para los forms**:
- Seguir mismo diseño de `registro.blade.php`
- Usar Tailwind CSS
- Validación visual con `@error`
- Botones "Guardar" y "Cancelar"
- Grid de 2 columnas en desktop

---

## 🔧 Mantenimiento

### Agregar Nuevas Cuotas
Si se extiende el período:

```php
DB::table('seccionk_eventos')->insert([
    'nombre' => 'Cuota Mensual Enero 2028',
    'descripcion' => 'Aporte mensual correspondiente a Enero 2028',
    'fecha' => '2028-01-01',
    'monto' => 50.00,
    'tipo' => 'cuota_mensual',
    'mes_cuota' => 'Enero 2028',
    'activo' => true,
    'created_at' => now(),
    'updated_at' => now(),
]);
```

### Cambiar Monto de Cuota
```php
SeccionKEvento::cuotasMensuales()->update(['monto' => 60.00]);
```

### Reportes Personalizados
```php
// Total recaudado por mes
$recaudado = SeccionKParticipacion::where('pago', true)
    ->join('seccionk_eventos', 'evento_id', '=', 'seccionk_eventos.id')
    ->where('tipo', 'cuota_mensual')
    ->selectRaw('mes_cuota, SUM(monto_aportado) as total')
    ->groupBy('mes_cuota')
    ->get();

// Morosidad
$morosos = SeccionKPromocion::where('participa_cuotas', true)
    ->whereDoesntHave('participaciones', function($q) {
        $q->where('pago', true);
    })
    ->get();
```

---

## 📞 Soporte

Para dudas o mejoras del módulo Sección K:
- Email: seccionk2001@gmail.com
- WhatsApp: +51 987 654 321

---

**Última actualización:** 2026-10-11  
**Versión:** 1.0  
**Estado:** Funcional - Pendiente vistas de formularios admin
