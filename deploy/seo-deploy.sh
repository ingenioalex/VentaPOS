#!/usr/bin/env bash
# Despliegue SEO a producción (ventasagil.com)
set -euo pipefail

VPS="${VPS:-root@2.25.166.12}"
APP="/var/www/ventapos"
LOCAL="$(cd "$(dirname "$0")/.." && pwd)"

echo "==> Subiendo archivos SEO..."
scp "$LOCAL/routes/web.php" "$VPS:$APP/routes/web.php"
scp "$LOCAL/config/seo.php" "$VPS:$APP/config/seo.php"
scp "$LOCAL/app/Providers/AppServiceProvider.php" "$VPS:$APP/app/Providers/AppServiceProvider.php"
scp "$LOCAL/app/Http/Controllers/LandingController.php" "$VPS:$APP/app/Http/Controllers/LandingController.php"
scp "$LOCAL/app/Http/Controllers/SeoController.php" "$VPS:$APP/app/Http/Controllers/SeoController.php"
scp "$LOCAL/resources/views/partials/seo-head.blade.php" "$VPS:$APP/resources/views/partials/seo-head.blade.php"
scp "$LOCAL/resources/views/seo/sitemap.blade.php" "$VPS:$APP/resources/views/seo/sitemap.blade.php"
scp "$LOCAL/resources/views/seo/robots.blade.php" "$VPS:$APP/resources/views/seo/robots.blade.php"
scp "$LOCAL/resources/views/landing/inicio.blade.php" "$VPS:$APP/resources/views/landing/inicio.blade.php"
scp "$LOCAL/resources/views/landing/faq.blade.php" "$VPS:$APP/resources/views/landing/faq.blade.php"
scp "$LOCAL/resources/views/layouts/guest.blade.php" "$VPS:$APP/resources/views/layouts/guest.blade.php"

echo "==> Limpiando cachés Laravel..."
ssh "$VPS" "cd $APP && php artisan route:clear && php artisan config:clear && php artisan view:cache"

echo "==> Sincronizando robots.txt (nginx sirve public/ antes que Laravel)..."
ADMIN_PATH="$(ssh "$VPS" "grep '^ADMIN_PANEL_PATH=' $APP/.env | cut -d= -f2-")"
ssh "$VPS" "cat > $APP/public/robots.txt" <<EOF
User-agent: *
Allow: /
Disallow: /app/
Disallow: /login
Disallow: /${ADMIN_PATH}/

Sitemap: https://ventasagil.com/sitemap.xml
EOF

echo "==> Verificación rápida..."
curl -sf "https://ventasagil.com/sitemap.xml" | head -5
curl -sf "https://ventasagil.com/robots.txt"
echo ""
echo "Despliegue SEO completado."
