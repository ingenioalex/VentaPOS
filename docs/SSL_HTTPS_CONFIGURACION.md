# Configuración SSL/HTTPS para VentaPos y Módulo SeccionK

## 🔒 Problema

El sitio muestra "No es seguro" porque usa HTTP sin cifrado SSL/TLS.

## ✅ Solución

### Opción 1: Desarrollo Local (Certificado Auto-firmado)

Para desarrollo local, crea un certificado SSL auto-firmado:

#### Windows (PowerShell como Administrador)

```powershell
# Crear directorio para certificados
New-Item -ItemType Directory -Force -Path C:\laragon\etc\ssl

# Generar certificado auto-firmado (válido 365 días)
$cert = New-SelfSignedCertificate -DnsName "ventasagil.com", "seccionk.ventasagil.com", "*.ventasagil.com" -CertStoreLocation "cert:\LocalMachine\My" -NotAfter (Get-Date).AddYears(1)

# Exportar certificado
$pwd = ConvertTo-SecureString -String "ventasagil2026" -Force -AsPlainText
Export-PfxCertificate -Cert "cert:\LocalMachine\My\$($cert.Thumbprint)" -FilePath "C:\laragon\etc\ssl\ventasagil.pfx" -Password $pwd

# Convertir a formato PEM (requiere OpenSSL)
# Si tienes OpenSSL instalado:
# openssl pkcs12 -in C:\laragon\etc\ssl\ventasagil.pfx -out C:\laragon\etc\ssl\ventasagil.crt -nokeys
# openssl pkcs12 -in C:\laragon\etc\ssl\ventasagil.pfx -out C:\laragon\etc\ssl\ventasagil.key -nodes -nocerts
```

#### Linux/Mac

```bash
# Crear directorio
sudo mkdir -p /etc/ssl/ventasagil

# Generar certificado auto-firmado
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/ssl/ventasagil/ventasagil.key \
  -out /etc/ssl/ventasagil/ventasagil.crt \
  -subj "/C=PE/ST=Lambayeque/L=Chiclayo/O=VentasAgil/CN=ventasagil.com" \
  -addext "subjectAltName=DNS:ventasagil.com,DNS:*.ventasagil.com,DNS:seccionk.ventasagil.com"

# Establecer permisos
sudo chmod 600 /etc/ssl/ventasagil/ventasagil.key
sudo chmod 644 /etc/ssl/ventasagil/ventasagil.crt
```

### Opción 2: Producción (Let's Encrypt - Certificado Gratis y Válido)

**Requisitos**:
- Dominio real apuntando a tu servidor
- Puerto 80 y 443 abiertos
- Servidor web (Nginx/Apache)

#### Nginx con Certbot

```bash
# 1. Instalar Certbot
sudo apt update
sudo apt install certbot python3-certbot-nginx -y

# 2. Obtener certificado SSL
sudo certbot --nginx -d ventasagil.com -d www.ventasagil.com -d seccionk.ventasagil.com

# 3. Renovación automática (ya configurada)
sudo certbot renew --dry-run
```

#### Apache con Certbot

```bash
# 1. Instalar Certbot
sudo apt update
sudo apt install certbot python3-certbot-apache -y

# 2. Obtener certificado SSL
sudo certbot --apache -d ventasagil.com -d www.ventasagil.com -d seccionk.ventasagil.com

# 3. Renovación automática
sudo certbot renew --dry-run
```

## 📝 Configuración del Servidor Web

### Nginx con SSL

```nginx
# /etc/nginx/sites-available/ventasagil.conf

# Redirigir HTTP a HTTPS
server {
    listen 80;
    server_name ventasagil.com www.ventasagil.com;
    
    # Redirigir todo el tráfico a HTTPS
    return 301 https://$server_name$request_uri;
}

# VentaPOS Principal - HTTPS
server {
    listen 443 ssl http2;
    server_name ventasagil.com www.ventasagil.com;
    
    root /var/www/ventasagil/public;
    index index.php index.html;

    # Certificados SSL
    ssl_certificate /etc/ssl/ventasagil/ventasagil.crt;
    ssl_certificate_key /etc/ssl/ventasagil/ventasagil.key;
    
    # Configuración SSL moderna
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384';
    ssl_prefer_server_ciphers off;
    
    # Headers de seguridad
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}

# Redirigir HTTP a HTTPS - SeccionK
server {
    listen 80;
    server_name seccionk.ventasagil.com;
    
    return 301 https://$server_name$request_uri;
}

# Módulo SeccionK - HTTPS
server {
    listen 443 ssl http2;
    server_name seccionk.ventasagil.com;
    
    root /var/www/ventasagil/public;
    index index.php index.html;

    # Certificados SSL (mismo certificado con wildcard)
    ssl_certificate /etc/ssl/ventasagil/ventasagil.crt;
    ssl_certificate_key /etc/ssl/ventasagil/ventasagil.key;
    
    # Configuración SSL moderna
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384';
    ssl_prefer_server_ciphers off;
    
    # Headers de seguridad
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Activar configuración**:
```bash
sudo ln -s /etc/nginx/sites-available/ventasagil.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Apache con SSL

```apache
# /etc/apache2/sites-available/ventasagil-ssl.conf

# Redirigir HTTP a HTTPS
<VirtualHost *:80>
    ServerName ventasagil.com
    ServerAlias www.ventasagil.com
    
    Redirect permanent / https://ventasagil.com/
</VirtualHost>

# VentaPOS Principal - HTTPS
<VirtualHost *:443>
    ServerName ventasagil.com
    ServerAlias www.ventasagil.com
    DocumentRoot /var/www/ventasagil/public

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/ssl/ventasagil/ventasagil.crt
    SSLCertificateKeyFile /etc/ssl/ventasagil/ventasagil.key
    
    # Protocolos y cifrados modernos
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite HIGH:!aNULL:!MD5
    SSLHonorCipherOrder on

    # Headers de seguridad
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"

    <Directory /var/www/ventasagil/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/ventasagil-error.log
    CustomLog ${APACHE_LOG_DIR}/ventasagil-access.log combined
</VirtualHost>

# Redirigir HTTP a HTTPS - SeccionK
<VirtualHost *:80>
    ServerName seccionk.ventasagil.com
    
    Redirect permanent / https://seccionk.ventasagil.com/
</VirtualHost>

# Módulo SeccionK - HTTPS
<VirtualHost *:443>
    ServerName seccionk.ventasagil.com
    DocumentRoot /var/www/ventasagil/public

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/ssl/ventasagil/ventasagil.crt
    SSLCertificateKeyFile /etc/ssl/ventasagil/ventasagil.key
    
    # Protocolos y cifrados modernos
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite HIGH:!aNULL:!MD5
    SSLHonorCipherOrder on

    # Headers de seguridad
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"

    <Directory /var/www/ventasagil/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/seccionk-error.log
    CustomLog ${APACHE_LOG_DIR}/seccionk-access.log combined
</VirtualHost>
```

**Activar configuración**:
```bash
# Habilitar módulos SSL
sudo a2enmod ssl
sudo a2enmod headers
sudo a2enmod rewrite

# Activar sitio
sudo a2ensite ventasagil-ssl.conf
sudo apache2ctl configtest
sudo systemctl reload apache2
```

## 🔧 Configuración de Laravel

Actualizar `.env` para forzar HTTPS:

```env
APP_URL=https://ventasagil.com

# Forzar HTTPS en producción
APP_FORCE_HTTPS=true
```

Agregar middleware en `app/Http/Middleware/TrustProxies.php`:

```php
protected $proxies = '*';

protected $headers = Request::HEADER_X_FORWARDED_ALL;
```

## 🌐 Desarrollo Local con HTTPS

### Laragon (Windows)

1. **Abrir Laragon → Menu → Apache → SSL → Crear certificado**
2. Seleccionar dominio: `ventasagil.com`
3. Laragon auto-configurará SSL
4. Acceder a: `https://ventasagil.com`

### Valet (Mac)

```bash
# Instalar certificado
valet secure ventasagil

# Para subdominios
valet secure seccionk
```

### Docker con mkcert

```bash
# Instalar mkcert
brew install mkcert  # Mac
choco install mkcert # Windows

# Generar certificado
mkcert ventasagil.com "*.ventasagil.com" localhost 127.0.0.1

# Usar en docker-compose.yml
volumes:
  - ./ventasagil.com+3.pem:/etc/ssl/certs/ventasagil.crt
  - ./ventasagil.com+3-key.pem:/etc/ssl/private/ventasagil.key
```

## ✅ Verificación

### 1. Probar certificado SSL

```bash
# Verificar que el certificado es válido
openssl s_client -connect ventasagil.com:443 -servername ventasagil.com

# Verificar subdominios
openssl s_client -connect seccionk.ventasagil.com:443 -servername seccionk.ventasagil.com
```

### 2. Verificar HTTPS en navegador

- ✅ Candado verde en la barra de direcciones
- ✅ Dice "Seguro" o "Conexión segura"
- ✅ Certificado válido al hacer clic en el candado

### 3. Verificar redirección HTTP → HTTPS

```bash
curl -I http://ventasagil.com
# Debe devolver: HTTP/1.1 301 Moved Permanently
# Location: https://ventasagil.com/

curl -I http://seccionk.ventasagil.com
# Debe devolver: HTTP/1.1 301 Moved Permanently
# Location: https://seccionk.ventasagil.com/
```

## 🔍 Troubleshooting

### Error: "Su conexión no es privada"

**Causa**: Certificado auto-firmado en desarrollo.

**Solución**: 
- Hacer clic en "Avanzado" → "Continuar a ventasagil.com"
- O instalar el certificado en el sistema como confiable

### Error: Mixed Content (HTTP/HTTPS)

**Causa**: Recursos cargando por HTTP en página HTTPS.

**Solución**: Verificar que todas las URLs usan HTTPS:

```php
// En config/app.php
'asset_url' => env('ASSET_URL', 'https://ventasagil.com'),
```

```blade
{{-- Usar helpers de Laravel --}}
<img src="{{ asset('images/logo.png') }}">
<script src="{{ secure_asset('js/app.js') }}"></script>
```

### Error: ERR_SSL_PROTOCOL_ERROR

**Causa**: Puerto 443 no está abierto o SSL no configurado.

**Solución**:
```bash
# Verificar que el puerto 443 está escuchando
sudo netstat -tlnp | grep :443

# Verificar firewall
sudo ufw allow 443/tcp
sudo ufw reload
```

## 📋 Checklist SSL

```
□ Certificado SSL generado
□ Certificado instalado en servidor web
□ Puerto 443 abierto en firewall
□ Configuración Nginx/Apache actualizada
□ HTTP redirige a HTTPS (301)
□ Headers de seguridad configurados
□ APP_URL usa https:// en .env
□ Laravel configurado para HTTPS
□ Verificado en navegador (candado verde)
□ Subdominio seccionk también con HTTPS
```

## 🎯 Recomendación Final

**Para desarrollo local**: Usa Laragon con SSL auto-firmado (más fácil)

**Para producción**: Usa Let's Encrypt con Certbot (gratis y automático)

---

**Última actualización:** 2026-10-11  
**Versión:** 1.0
