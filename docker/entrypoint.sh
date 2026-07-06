#!/bin/bash
set -e

# Espera a que la base de datos acepte conexiones (evita que el primer deploy falle
# si Coolify levanta la app antes de que MySQL termine de arrancar).
if [ -n "$DB_HOST" ]; then
    echo "Esperando MySQL en ${DB_HOST}:${DB_PORT:-3306}..."
    for i in $(seq 1 30); do
        if (echo > "/dev/tcp/${DB_HOST}/${DB_PORT:-3306}") >/dev/null 2>&1; then
            echo "MySQL disponible."
            break
        fi
        sleep 2
    done
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"
