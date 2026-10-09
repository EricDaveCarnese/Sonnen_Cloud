#!/bin/bash
set -e

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Caching configs..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Starting Apache..."
apache2-foreground