# 🎓 Módulo Sección K - Promoción 2001

## 🚀 Inicio Rápido

### 1. Ejecutar Migraciones
```bash
php artisan migrate
php artisan db:seed --class=SeccionKSeeder
```

### 2. Configurar Subdominio Local
**Windows** - Editar `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1   seccionk.ventasagil.com
```

**Linux/Mac** - Editar `/etc/hosts`:
```
127.0.0.1   seccionk.ventasagil.com
```

### 3. Acceder
- **Portal Público**: http://seccionk.ventasagil.com
- **Panel Admin**: http://seccionk.ventasagil.com/admin/login

### 4. Credenciales Admin
```
Usuario: admin
Contraseña: seccionk2026
```

### 5. Datos de Prueba
```
DNI: 12345678
Apellido: García
```

---

## 📋 Funcionalidades

### ✅ Portal Público
- [x] Consulta por DNI y apellido
- [x] Perfil con línea de tiempo de cuotas mensuales
- [x] Visualización de eventos y participaciones
- [x] Registro de nuevos miembros
- [x] Diseño responsivo y profesional

### ✅ Panel Administrativo
- [x] Autenticación independiente
- [x] Dashboard con estadísticas
- [x] Gestión completa de promociones (CRUD)
- [x] Gestión completa de eventos (CRUD)
- [x] Registro de participaciones y pagos
- [x] Subida de imágenes de sustento
- [x] Filtros y búsquedas avanzadas

---

## 🗄️ Base de Datos

- **seccionk_promociones**: Miembros de la promoción
- **seccionk_eventos**: Eventos y cuotas mensuales
- **seccionk_participaciones**: Participación y pagos
- **seccionk_usuarios**: Administradores del panel

---

## 📖 Documentación Completa

Ver: [docs/SECCIONK_MODULO.md](../docs/SECCIONK_MODULO.md)

---

## 🎨 Tecnologías

- **Backend**: Laravel 13, PHP 8.3
- **Frontend**: Blade, Tailwind CSS 4.0, Font Awesome 6.4
- **Base de Datos**: MySQL 8 / SQLite
- **Autenticación**: Laravel Guards (separado de VentaPOS)

---

## 🔧 Configuración Producción

### Nginx
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

### DNS
```
Tipo: A
Host: seccionk
Valor: [IP del servidor]
TTL: 3600
```

---

## 📞 Contacto

Para más información sobre el módulo Sección K:
- Email: seccionk2001@gmail.com
- WhatsApp: +51 987 654 321

---

**Versión**: 1.0  
**Fecha**: 2026-10-11  
**Estado**: ✅ Funcional (pendiente vistas de formularios admin)
