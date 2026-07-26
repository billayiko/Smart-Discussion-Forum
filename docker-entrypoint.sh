#!/bin/sh
set -e

# Render (and most container hosts) assign the listen port via $PORT at runtime.
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY is not set. Generate one locally with 'php artisan key:generate --show' and set it in your host's environment variables."
fi

mkdir -p database
touch database/database.sqlite

php artisan migrate --force --no-interaction || echo "WARNING: migrations failed to run."

exec "$@"
