#!/bin/bash
set -e

echo "=== Adding Ondrej PHP PPA ==="
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update

echo "=== Installing system dependencies (PHP 8.4) ==="
sudo apt install -y php8.4-cli php8.4-xml php8.4-mbstring php8.4-pgsql php8.4-curl php8.4-zip unzip curl postgresql postgresql-contrib redis-server

echo "=== Installing Composer ==="
if ! command -v composer &> /dev/null; then 
    curl -sS https://getcomposer.org/installer | php 
    sudo mv composer.phar /usr/local/bin/composer
else 
    echo "Composer is already installed."
fi

echo "=== Installing project dependencies ==="
composer install