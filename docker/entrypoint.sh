#!/bin/sh
set -e

# Railway assigns the port at runtime via $PORT — Apache must bind to it.
PORT="${PORT:-80}"
sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

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

exec "$@"
