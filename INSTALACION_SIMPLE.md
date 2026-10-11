# 📦 Instalación Simple de SeccionK en VentaPOS

## 🎯 Instrucciones para Instalar en D:\Proyectos\VentaPOS_build

### Paso 1: Descargar los archivos necesarios

Descarga estos archivos del repositorio y cópialos a tu proyecto:

**Desde:** https://github.com/ingenioalex/VentaPOS/tree/cursor/seccionk-module-0c7c

**Copiar a tu proyecto `D:\Proyectos\VentaPOS_build`:**

```
app/
├── Http/
│   ├── Controllers/
│   │   └── SeccionK/           ← COPIAR ESTA CARPETA
│   └── Middleware/
│       └── SeccionKAuth.php    ← COPIAR ESTE ARCHIVO
└── Models/
    ├── SeccionKPromocion.php   ← COPIAR
    ├── SeccionKEvento.php      ← COPIAR
    ├── SeccionKParticipacion.php ← COPIAR
    └── SeccionKUsuario.php     ← COPIAR

resources/views/
└── seccionk/                   ← COPIAR TODA ESTA CARPETA

routes/
└── seccionk.php                ← COPIAR ESTE ARCHIVO

database/
├── migrations/
│   └── 2026_10_11_000001_crear_tablas_seccionk.php  ← COPIAR
└── seeders/
    └── SeccionKSeeder.php      ← COPIAR
```

### Paso 2: Modificar archivos existentes

#### 2.1 Editar `bootstrap/app.php`

Busca la sección `->withRouting(` y agrega las rutas de SeccionK:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
    then: function () {
        Route::middleware('web')
            ->group(base_path('routes/admin.php'));
        
        // ⭐ AGREGAR ESTAS LÍNEAS:
        Route::middleware('web')
            ->prefix('seccionk')
            ->name('seccionk.')
            ->group(base_path('routes/seccionk.php'));
    },
)
```

También busca la sección `->withMiddleware(` y agrega el alias:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        \App\Http\Middleware\IdentificarEmpresa::class,
    ]);
    $middleware->alias([
        'modulo' => \App\Http\Middleware\VerificarModulo::class,
        'empresa' => \App\Http\Middleware\AsegurarEmpresa::class,
        'dueno' => \App\Http\Middleware\SoloDueno::class,
        'super_admin' => \App\Http\Middleware\SoloSuperAdmin::class,
        'onboarding' => \App\Http\Middleware\RequiereOnboarding::class,
        'seccionk.auth' => \App\Http\Middleware\SeccionKAuth::class,  // ⭐ AGREGAR ESTA LÍNEA
    ]);
    // ... resto del código
})
```

#### 2.2 Editar `config/auth.php`

Busca el array `'guards'` y agrega:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    
    // ⭐ AGREGAR ESTO:
    'seccionk' => [
        'driver' => 'session',
        'provider' => 'seccionk',
    ],
],
```

Busca el array `'providers'` y agrega:

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
    
    // ⭐ AGREGAR ESTO:
    'seccionk' => [
        'driver' => 'eloquent',
        'model' => App\Models\SeccionKUsuario::class,
    ],
],
```

#### 2.3 Editar `database/seeders/DatabaseSeeder.php`

Agrega la llamada al seeder de SeccionK:

```php
public function run(): void
{
    // ... tus seeders actuales ...
    
    // ⭐ AGREGAR ESTA LÍNEA:
    $this->call(SeccionKSeeder::class);
}
```

### Paso 3: Ejecutar comandos

Abre una terminal en `D:\Proyectos\VentaPOS_build` y ejecuta:

```cmd
php artisan migrate
php artisan db:seed --class=SeccionKSeeder
php artisan config:cache
php artisan route:cache
```

### Paso 4: ¡Listo!

Inicia tu servidor (si no está corriendo):

```cmd
php artisan serve
```

Abre tu navegador en:
- **SeccionK Público:** http://localhost:8000/seccionk
- **SeccionK Admin:** http://localhost:8000/seccionk/admin/login

---

## 🔐 Credenciales

**Admin:**
- Usuario: `admin`
- Contraseña: `seccionk2026`

**Búsqueda:**
- DNI: `12345678`
- Apellido: `García`

---

## 📋 Alternativa: Usar Git

Si prefieres, puedes hacer merge de la branch desde GitHub:

```cmd
cd D:\Proyectos\VentaPOS_build
git fetch origin
git merge origin/cursor/seccionk-module-0c7c
php artisan migrate
php artisan db:seed --class=SeccionKSeeder
php artisan config:cache
php artisan route:cache
```

---

## ✅ Verificar que funciona

```cmd
php artisan route:list | findstr seccionk
```

Deberías ver todas las rutas de SeccionK.

---

**Nota:** SeccionK usa su propia base de datos (tablas `seccionk_*`) completamente separada de VentaPOS.
