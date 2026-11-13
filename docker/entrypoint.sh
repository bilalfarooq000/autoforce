#!/bin/sh
set -e

# Point the app's uploaded-file storage at the mounted volume, if one is attached.
if [ -d /data ]; then
    mkdir -p /data/app/public
    rm -rf storage/app/public
    ln -sfn /data/app/public storage/app/public
fi

php artisan storage:link --force || true

# Config is cached at runtime (not build time) so it picks up Railway's env vars.
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    php artisan db:seed --class=RoleSeeder --force
    php artisan db:seed --class=DemoDataSeeder --force
    php artisan db:seed --class=InventorySeeder --force
fi

php-fpm -D

exec "$@"
