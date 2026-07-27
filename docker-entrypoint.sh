#!/bin/sh
set -e

# Render (and most container hosts) assign the listen port via $PORT at runtime.
PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY is not set. Generate one locally with 'php artisan key:generate --show' and set it in your host's environment variables."
fi

# Only bootstrap a SQLite file when actually using the sqlite driver — on
# Postgres (DB_CONNECTION=pgsql + DB_URL set), this would just leave behind
# an unused, empty database.sqlite that nothing reads from.
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    mkdir -p database
    touch database/database.sqlite
fi

php artisan migrate --force --no-interaction || echo "WARNING: migrations failed to run."

exec "$@"
