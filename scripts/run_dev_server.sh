#!/bin/bash

if [ ! -d "public" ]; then
    echo "Error: The directory 'public/' does not exist. Creating it..."
    mkdir -p public
fi

echo "Development server started at http://localhost:8000"
echo ==============================
echo =   PRESS CTRL + C TO EXIT   =
echo ==============================
php -S localhost:8000 -t public/