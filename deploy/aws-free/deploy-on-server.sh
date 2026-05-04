#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/mientayshop}"

cd "$APP_DIR"

echo "==> Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Creating production .env if missing..."
if [ ! -f .env ]; then
  cp deploy/aws-free/.env.production.example .env
fi

if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force
fi

echo "==> Preparing Laravel storage..."
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
if [ -d public/storage ] && [ ! -L public/storage ]; then
  rm -rf public/storage
fi
php artisan storage:link || true

echo "==> Preparing SQLite database..."
mkdir -p database
if [ ! -f database/database.sqlite ]; then
  touch database/database.sqlite
fi

php artisan migrate --force

echo "==> Optimizing Laravel..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Fixing permissions..."
sudo usermod -a -G www-data "$USER" || true
sudo chown -R "$USER":www-data storage bootstrap/cache database
sudo find storage bootstrap/cache database -type d -exec chmod 775 {} \;
sudo find storage bootstrap/cache database -type f -exec chmod 664 {} \;

sudo systemctl restart php8.1-fpm
sudo systemctl reload nginx

echo "Deploy finished. Open your EC2 public IPv4 in the browser."
