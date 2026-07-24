#!/usr/bin/env bash
# Randevall — guncelleme deploy (git pull sonrasi)
set -euo pipefail

APP_DIR="${APP_DIR:-$(pwd)}"
cd "$APP_DIR"

git pull origin master
composer install --no-dev --optimize-autoloader --no-interaction

if command -v npm >/dev/null 2>&1; then
  npm ci
  npm run build
else
  echo "UYARI: npm yok — public/build yerelde build edilip yuklenmeli."
fi

php artisan migrate --force
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan event:clear
php artisan queue:restart || true

echo "Deploy tamamlandi."
