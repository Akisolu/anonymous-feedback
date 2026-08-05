#!/bin/bash
set -e

echo "=== Installing system dependencies ==="
sudo apt install -y php-cli php-xml php-mbstring php-pgsql php-curl unzip curl postgresql postgresql-contrib redis-server

echo "=== Installing Composer ==="
if ! command -v composer &> /dev/null; then 
curl -sS https://getcomposer.org/installer | php 
sudo mv composer.phar /usr/local/bin/composer
else 
echo "Composer is already installed."
fi

echo "=== Installing project dependencies (includes PHPUnit) ==="
composer install