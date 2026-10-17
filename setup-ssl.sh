#!/bin/bash

# Script para configurar SSL/HTTPS en desarrollo local
# Para VentaPos y Módulo SeccionK

echo "╔════════════════════════════════════════════════════════════╗"
echo "║  Configuración SSL/HTTPS para VentasAgil                  ║"
echo "║  Desarrollo Local con Certificado Auto-firmado            ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verificar que se ejecuta como root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}❌ Este script debe ejecutarse como root (sudo)${NC}" 
   exit 1
fi

# 1. Crear directorio para certificados
echo -e "${YELLOW}[1/6]${NC} Creando directorio para certificados..."
mkdir -p /etc/ssl/ventasagil
chmod 755 /etc/ssl/ventasagil

# 2. Generar certificado SSL auto-firmado
echo -e "${YELLOW}[2/6]${NC} Generando certificado SSL auto-firmado..."

openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/ssl/ventasagil/ventasagil.key \
  -out /etc/ssl/ventasagil/ventasagil.crt \
  -subj "/C=PE/ST=Lambayeque/L=Chiclayo/O=VentasAgil/CN=ventasagil.com" \
  -addext "subjectAltName=DNS:ventasagil.com,DNS:www.ventasagil.com,DNS:*.ventasagil.com,DNS:seccionk.ventasagil.com,DNS:localhost" \
  2>/dev/null

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Certificado generado exitosamente${NC}"
else
    echo -e "${RED}❌ Error al generar certificado${NC}"
    exit 1
fi

# 3. Establecer permisos
echo -e "${YELLOW}[3/6]${NC} Configurando permisos..."
chmod 600 /etc/ssl/ventasagil/ventasagil.key
chmod 644 /etc/ssl/ventasagil/ventasagil.crt
echo -e "${GREEN}✓ Permisos configurados${NC}"

# 4. Detectar servidor web
echo -e "${YELLOW}[4/6]${NC} Detectando servidor web..."

if command -v nginx &> /dev/null; then
    SERVER="nginx"
    echo -e "${GREEN}✓ Nginx detectado${NC}"
elif command -v apache2 &> /dev/null || command -v httpd &> /dev/null; then
    SERVER="apache"
    echo -e "${GREEN}✓ Apache detectado${NC}"
else
    echo -e "${YELLOW}⚠ No se detectó Nginx ni Apache${NC}"
    echo "Certificados generados en: /etc/ssl/ventasagil/"
    echo "Configura manualmente tu servidor web"
    exit 0
fi

# 5. Configurar servidor web
echo -e "${YELLOW}[5/6]${NC} Configurando $SERVER..."

if [ "$SERVER" = "nginx" ]; then
    # Crear configuración Nginx
    cat > /etc/nginx/sites-available/ventasagil-ssl.conf <<'EOF'
# Redirigir HTTP a HTTPS
server {
    listen 80;
    server_name ventasagil.com www.ventasagil.com;
    return 301 https://$server_name$request_uri;
}

# VentaPOS Principal - HTTPS
server {
    listen 443 ssl http2;
    server_name ventasagil.com www.ventasagil.com;
    
    root /var/www/ventasagil/public;
    index index.php index.html;

    ssl_certificate /etc/ssl/ventasagil/ventasagil.crt;
    ssl_certificate_key /etc/ssl/ventasagil/ventasagil.key;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers off;
    
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

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

    ssl_certificate /etc/ssl/ventasagil/ventasagil.crt;
    ssl_certificate_key /etc/ssl/ventasagil/ventasagil.key;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers off;
    
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

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
EOF

    # Activar sitio
    ln -sf /etc/nginx/sites-available/ventasagil-ssl.conf /etc/nginx/sites-enabled/
    
    # Verificar configuración
    nginx -t &>/dev/null
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Configuración Nginx válida${NC}"
    else
        echo -e "${RED}❌ Error en configuración Nginx${NC}"
        nginx -t
        exit 1
    fi

elif [ "$SERVER" = "apache" ]; then
    # Habilitar módulos necesarios
    a2enmod ssl headers rewrite &>/dev/null
    
    # Crear configuración Apache
    cat > /etc/apache2/sites-available/ventasagil-ssl.conf <<'EOF'
<VirtualHost *:80>
    ServerName ventasagil.com
    ServerAlias www.ventasagil.com
    Redirect permanent / https://ventasagil.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName ventasagil.com
    ServerAlias www.ventasagil.com
    DocumentRoot /var/www/ventasagil/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/ventasagil/ventasagil.crt
    SSLCertificateKeyFile /etc/ssl/ventasagil/ventasagil.key
    
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite HIGH:!aNULL:!MD5
    SSLHonorCipherOrder on

    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

    <Directory /var/www/ventasagil/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

<VirtualHost *:80>
    ServerName seccionk.ventasagil.com
    Redirect permanent / https://seccionk.ventasagil.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName seccionk.ventasagil.com
    DocumentRoot /var/www/ventasagil/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/ventasagil/ventasagil.crt
    SSLCertificateKeyFile /etc/ssl/ventasagil/ventasagil.key
    
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite HIGH:!aNULL:!MD5
    SSLHonorCipherOrder on

    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

    <Directory /var/www/ventasagil/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
EOF

    # Activar sitio
    a2ensite ventasagil-ssl.conf &>/dev/null
    
    # Verificar configuración
    apache2ctl configtest &>/dev/null
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Configuración Apache válida${NC}"
    else
        echo -e "${RED}❌ Error en configuración Apache${NC}"
        apache2ctl configtest
        exit 1
    fi
fi

# 6. Reiniciar servidor web
echo -e "${YELLOW}[6/6]${NC} Reiniciando servidor web..."

if [ "$SERVER" = "nginx" ]; then
    systemctl reload nginx
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Nginx reiniciado${NC}"
    else
        echo -e "${RED}❌ Error al reiniciar Nginx${NC}"
        exit 1
    fi
elif [ "$SERVER" = "apache" ]; then
    systemctl reload apache2 || systemctl reload httpd
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Apache reiniciado${NC}"
    else
        echo -e "${RED}❌ Error al reiniciar Apache${NC}"
        exit 1
    fi
fi

# Resumen
echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✅ SSL/HTTPS CONFIGURADO EXITOSAMENTE                    ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "📁 Certificados generados en:"
echo -e "   - Certificado: /etc/ssl/ventasagil/ventasagil.crt"
echo -e "   - Clave privada: /etc/ssl/ventasagil/ventasagil.key"
echo ""
echo -e "🌐 Sitios configurados:"
echo -e "   - ${GREEN}https://ventasagil.com${NC}"
echo -e "   - ${GREEN}https://seccionk.ventasagil.com${NC}"
echo ""
echo -e "⚠️  ${YELLOW}IMPORTANTE:${NC}"
echo -e "   Este es un certificado AUTO-FIRMADO para desarrollo."
echo -e "   El navegador mostrará una advertencia de seguridad."
echo -e "   Haz clic en 'Avanzado' → 'Continuar al sitio'"
echo ""
echo -e "   Para producción, usa Let's Encrypt:"
echo -e "   ${GREEN}sudo certbot --nginx -d ventasagil.com -d seccionk.ventasagil.com${NC}"
echo ""
echo -e "📝 Configuración Laravel:"
echo -e "   Actualiza tu .env:"
echo -e "   ${YELLOW}APP_URL=https://ventasagil.com${NC}"
echo ""

# Verificar que los puertos están abiertos
echo -e "🔍 Verificando puertos..."
if netstat -tlnp 2>/dev/null | grep -q ':443'; then
    echo -e "   ${GREEN}✓ Puerto 443 (HTTPS) está escuchando${NC}"
else
    echo -e "   ${RED}❌ Puerto 443 (HTTPS) NO está escuchando${NC}"
fi

if netstat -tlnp 2>/dev/null | grep -q ':80'; then
    echo -e "   ${GREEN}✓ Puerto 80 (HTTP) está escuchando${NC}"
else
    echo -e "   ${RED}❌ Puerto 80 (HTTP) NO está escuchando${NC}"
fi

echo ""
echo -e "${GREEN}✨ ¡Listo! Ahora puedes acceder de forma segura (HTTPS)${NC}"
