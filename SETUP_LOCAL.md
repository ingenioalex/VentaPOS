# 🖥️ Guía de Instalación y Prueba en Local

## 📋 Requisitos Previos

### Windows (Recomendado: Laragon)

**Opción 1: Laragon (Todo en uno - FÁCIL)**
1. Descargar Laragon Full: https://laragon.org/download/
2. Instalar (incluye PHP 8.3, MySQL, Node.js)
3. Listo!

**Opción 2: Manual**
- PHP 8.2+: https://www.php.net/downloads
- Composer: https://getcomposer.org/download/
- Node.js (opcional): https://nodejs.org/

### Linux / Mac

```bash
# Ubuntu/Debian
sudo apt install php8.3 php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl php8.3-sqlite3

# Mac (Homebrew)
brew install php composer
```

---

## 🚀 Instalación Rápida

### Paso 1: Clonar el repositorio

```bash
git clone https://github.com/ingenioalex/VentaPOS.git
cd VentaPOS
git checkout cursor/seccionk-module-0c7c
```

### Paso 2: Ejecutar script de setup

**Windows:**
```cmd
setup-local.bat
```

**Linux/Mac:**
```bash
chmod +x setup-local.sh
./setup-local.sh
```

### Paso 3: Iniciar servidor

```bash
php artisan serve
```

### Paso 4: Abrir navegador

Abre tu navegador en: **http://localhost:8000/seccionk**

---

## 📝 Instalación Manual (Paso a Paso)

Si prefieres hacerlo manualmente:

### 1. Instalar dependencias PHP

```bash
composer install
```

### 2. Configurar .env

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configurar base de datos SQLite

```bash
# Linux/Mac
touch database/database.sqlite

# Windows (PowerShell)
New-Item database/database.sqlite -ItemType File

# Windows (CMD)
type nul > database\database.sqlite
```

Asegúrate que `.env` tenga:
```env
DB_CONNECTION=sqlite
```

### 4. Ejecutar migraciones

```bash
php artisan migrate
```

### 5. Cargar datos de prueba

```bash
php artisan db:seed
```

### 6. (Opcional) Construir assets

```bash
npm install
npm run build
```

### 7. Iniciar servidor

```bash
php artisan serve
```

---

## 🌐 URLs de Acceso

Después de iniciar el servidor:

| Sistema | URL |
|---------|-----|
| **VentaPOS Principal** | http://localhost:8000 |
| **SeccionK Público** | http://localhost:8000/seccionk |
| **SeccionK Admin** | http://localhost:8000/seccionk/admin/login |

---

## 🔐 Credenciales de Prueba

### SeccionK Admin

```
URL:        http://localhost:8000/seccionk/admin/login
Usuario:    admin
Contraseña: seccionk2026
```

### SeccionK - Búsqueda Pública

```
URL:      http://localhost:8000/seccionk
DNI:      12345678
Apellido: García
```

Otros miembros de prueba:
- DNI: `87654321` Apellido: `López`
- DNI: `11223344` Apellido: `Rodríguez`

---

## ✅ Qué Probar

### 1️⃣ Búsqueda Pública

1. Ve a: http://localhost:8000/seccionk
2. Ingresa DNI: `12345678`
3. Ingresa Apellido: `García`
4. Click "Buscar"

**Resultado esperado:**
- Aparece el perfil de Juan García
- Timeline con 15 cuotas mensuales (Oct 2026 - Dic 2027)
- Lista de eventos con participación
- Estadísticas de pagos

### 2️⃣ Panel Administrativo

1. Ve a: http://localhost:8000/seccionk/admin/login
2. Usuario: `admin`, Contraseña: `seccionk2026`
3. Click "Iniciar Sesión"

**Dashboard (pantalla principal):**
- Total de miembros activos
- Recaudación mensual
- Tasa de participación en eventos
- Lista de próximos eventos

**Gestión de Promociones (Miembros):**
1. Click "Promociones" en el menú
2. Ver lista de 3 miembros
3. Click "Ver" en Juan García
4. Click "Editar"
5. Cambia algo (ejemplo: celular)
6. Guarda

**Gestión de Eventos:**
1. Click "Eventos" en el menú
2. Ver lista de eventos (15 cuotas + 1 evento deportivo)
3. Click "Nuevo Evento"
4. Completa formulario:
   - Tipo: Fútbol
   - Descripción: "Campeonato Octubre"
   - Monto: 10.00
   - Fecha: hoy
5. Guarda
6. Click "Participantes" en el evento
7. Selecciona miembros
8. Ingresa montos pagados
9. Guarda

### 3️⃣ Registro de Nuevos Miembros

1. Ve a: http://localhost:8000/seccionk/registro
2. Completa formulario:
   - Nombre: Tu nombre
   - Apellidos: Tus apellidos
   - DNI: 99999999
   - Dirección: Tu dirección
   - Celular: 987654321
   - ¿Participa en cuotas?: Sí
3. Registra
4. Busca tu DNI en la búsqueda pública

---

## 🎨 Características Visuales

### Timeline Interactivo
- 15 meses de cuotas (Oct 2026 - Dic 2027)
- Iconos verdes ✓ para pagado
- Iconos rojos ✗ para pendiente
- Montos y fechas

### Diseño Responsive
- Prueba en diferentes tamaños de ventana
- Mobile: menú hamburguesa
- Tablet: 2 columnas
- Desktop: 3 columnas

### Gradiente Púrpura
- Navbar con gradiente (#667eea → #764ba2)
- Cards con sombras
- Hover effects en botones

---

## 🔧 Comandos Útiles

### Ver todas las rutas de SeccionK

```bash
php artisan route:list --path=seccionk
```

### Resetear base de datos

```bash
php artisan migrate:fresh --seed
```

### Ver logs

```bash
tail -f storage/logs/laravel.log
```

### Limpiar caché

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Recrear caché

```bash
php artisan config:cache
php artisan route:cache
```

---

## 🐛 Solución de Problemas

### Error: "Class not found"

```bash
composer dump-autoload
php artisan config:clear
```

### Error: "Database not found"

```bash
# Verifica que database/database.sqlite existe
ls -la database/database.sqlite

# Si no existe, créalo:
touch database/database.sqlite
php artisan migrate
```

### Error: "Route not found"

```bash
php artisan route:clear
php artisan route:cache
php artisan serve
```

### Error: "Permission denied" (Linux/Mac)

```bash
chmod -R 775 storage bootstrap/cache
```

### Assets no cargan (estilos)

Los assets usan CDN (Tailwind CSS, Font Awesome), no necesitas `npm run build`.

Si quieres usar assets locales:
```bash
npm install
npm run build
```

### Puerto 8000 ocupado

```bash
php artisan serve --port=8080
```

Luego abre: http://localhost:8080/seccionk

---

## 📱 Probar en Dispositivos Móviles

### En la misma red local

1. Encuentra tu IP local:

**Windows:**
```cmd
ipconfig
```
Busca: "IPv4 Address"

**Linux/Mac:**
```bash
ifconfig
```
Busca: "inet" (ejemplo: 192.168.1.100)

2. Inicia servidor en todas las interfaces:

```bash
php artisan serve --host=0.0.0.0
```

3. En tu móvil, abre:
```
http://TU_IP_LOCAL:8000/seccionk
```

Ejemplo: `http://192.168.1.100:8000/seccionk`

---

## 📊 Datos de Prueba Incluidos

### Miembros (3)
- Juan García (DNI: 12345678)
- María López (DNI: 87654321)
- Carlos Rodríguez (DNI: 11223344)

### Eventos (16)
- 15 cuotas mensuales (S/. 50.00 cada una)
- 1 evento deportivo: Fulbito Relámpago (S/. 5.00)

### Usuario Admin (1)
- Usuario: admin
- Contraseña: seccionk2026

---

## 🎯 Checklist de Prueba

Marca lo que pruebes:

### Público
- [ ] Búsqueda por DNI + apellido
- [ ] Ver perfil con timeline
- [ ] Ver eventos y participaciones
- [ ] Registro de nuevo miembro

### Admin
- [ ] Login en panel admin
- [ ] Ver dashboard con estadísticas
- [ ] Listar miembros
- [ ] Crear miembro
- [ ] Editar miembro
- [ ] Ver miembro
- [ ] Listar eventos
- [ ] Crear evento
- [ ] Asignar participantes a evento
- [ ] Registrar pagos
- [ ] Logout

### Diseño
- [ ] Timeline se ve bien
- [ ] Responsive en móvil
- [ ] Responsive en tablet
- [ ] Iconos cargan (Font Awesome)
- [ ] Gradiente púrpura en navbar
- [ ] Cards con hover effects

---

## 📸 Capturas de Pantalla

Después de probar, deberías ver:

1. **Búsqueda:** Formulario con campos DNI y Apellido
2. **Perfil:** Timeline vertical con 15 cuotas + eventos
3. **Admin Login:** Formulario elegante con gradiente
4. **Dashboard:** 4 cards con estadísticas + tabla de eventos
5. **Lista Miembros:** Tabla con acciones (Ver/Editar)
6. **Lista Eventos:** Tabla con fechas y montos

---

## 🚀 Siguiente Paso

Una vez que hayas probado en local y esté todo OK:

1. **Desplegar a VPS** siguiendo: `DESPLEGAR_AQUI.md`
2. O lee: `INSTRUCCIONES_DESPLIEGUE.md`

---

## 📞 Soporte

Si algo no funciona:

1. Verifica que PHP 8.2+ esté instalado
2. Revisa `storage/logs/laravel.log`
3. Ejecuta `php artisan route:list | grep seccionk`
4. Limpia caché con `php artisan config:clear`

---

**Archivo creado:** Octubre 2026  
**Branch:** cursor/seccionk-module-0c7c  
**Estado:** ✅ Listo para probar
