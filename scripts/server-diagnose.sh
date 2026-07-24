#!/usr/bin/env bash
# dis.randevall.com 502 teshisi — sunucuda SSH ile calistirin
# bash scripts/server-diagnose.sh /home/cloudpanel/htdocs/dis.randevall.com

APP_DIR="${1:-.}"
cd "$APP_DIR" 2>/dev/null || { echo "Dizin bulunamadi: $APP_DIR"; exit 1; }

echo "=== Dizin ==="
pwd
ls -la | head -20

echo ""
echo "=== public/index.php ==="
if [ -f public/index.php ]; then echo "OK: public/index.php var"; else echo "HATA: public/index.php YOK — Document Root yanlis olabilir"; fi

echo ""
echo "=== .env ==="
if [ -f .env ]; then grep -E '^APP_|^DB_' .env | sed 's/DB_PASSWORD=.*/DB_PASSWORD=***/'; else echo "HATA: .env yok"; fi

echo ""
echo "=== PHP ==="
php -v 2>/dev/null | head -1 || echo "php komutu yok"

echo ""
echo "=== Composer vendor ==="
[ -d vendor ] && echo "OK: vendor/" || echo "HATA: vendor/ yok — composer install calistirin"

echo ""
echo "=== public/build ==="
if [ -f public/build/manifest.json ]; then
  echo "OK: Vite build var"
  if grep -q 'panel-pwa.js' public/build/manifest.json 2>/dev/null; then
    echo "OK: panel-pwa.js manifest'te"
  else
    echo "UYARI: panel-pwa.js manifest'te YOK — Windows'tan public/build yukleyin veya npm run build"
  fi
else
  echo "UYARI: public/build yok — npm run build gerekli"
fi

echo ""
echo "=== Izinler ==="
ls -ld storage bootstrap/cache 2>/dev/null

echo ""
echo "=== Laravel test ==="
php artisan --version 2>&1 || echo "artisan calismadi"

echo ""
echo "=== PHP-FPM (varsa) ==="
systemctl is-active php8.3-fpm 2>/dev/null || systemctl is-active php8.2-fpm 2>/dev/null || echo "php-fpm durumu kontrol edilemedi (root gerekebilir)"

echo ""
echo "=== Son nginx/php hatalari (CloudPanel) ==="
for f in /home/cloudpanel/logs/*/error.log /var/log/nginx/error.log; do
  [ -f "$f" ] && echo "--- $f ---" && tail -5 "$f" 2>/dev/null
done
