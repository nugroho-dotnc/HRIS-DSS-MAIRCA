#!/bin/bash
set -e

mkdir -p storage/app/livewire-tmp storage/logs storage/framework/cache storage/framework/sessions storage/framework/views
chmod -R 775 storage bootstrap/cache

php artisan storage:link --force
php artisan migrate --force
php artisan config:cache
php artisan route:cache

frankenphp run --config /etc/caddy/Caddyfile
