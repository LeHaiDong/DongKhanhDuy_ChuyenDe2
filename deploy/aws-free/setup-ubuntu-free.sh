#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/mientayshop"
APP_URL="${APP_URL:-http://$(curl -s http://169.254.169.254/latest/meta-data/public-ipv4 || echo YOUR_EC2_PUBLIC_IP)}"

echo "==> Installing Nginx, PHP, SQLite and Composer..."
sudo apt-get update
sudo apt-get install -y nginx unzip curl git sqlite3 \
  php8.1-fpm php8.1-cli php8.1-sqlite3 php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-bcmath

if ! command -v composer >/dev/null 2>&1; then
  EXPECTED_SIGNATURE="$(curl -s https://composer.github.io/installer.sig)"
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

  if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then
    rm composer-setup.php
    echo "Composer installer signature mismatch"
    exit 1
  fi

  php composer-setup.php --quiet
  sudo mv composer.phar /usr/local/bin/composer
  rm composer-setup.php
fi

echo "==> Preparing app directory..."
sudo mkdir -p "$APP_DIR"
sudo chown -R "$USER:www-data" "$APP_DIR"

echo "==> Installing Nginx config..."
sudo cp ./deploy/aws-free/nginx-mientayshop.conf /etc/nginx/sites-available/mientayshop
sudo ln -sf /etc/nginx/sites-available/mientayshop /etc/nginx/sites-enabled/mientayshop
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx

cat <<INFO

Server is ready.

Use these values in .env:
APP_URL=${APP_URL}
DB_CONNECTION=sqlite
DB_DATABASE=${APP_DIR}/database/database.sqlite

Next: upload project files into ${APP_DIR}, then run:
  bash deploy/aws-free/deploy-on-server.sh

INFO
