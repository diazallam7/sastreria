#!/bin/bash
set -e

# Si se configuró DB_URL (ej. Coolify) en vez de las variables sueltas, sacamos
# host/puerto de ahí para poder esperar a que la base de datos esté lista.
DB_WAIT_HOST="$DB_HOST"
DB_WAIT_PORT="${DB_PORT:-3306}"
if [ -z "$DB_WAIT_HOST" ] && [ -n "$DB_URL" ]; then
    # mysql://user:pass@host:port/db -> host y puerto
    DB_WAIT_HOST=$(echo "$DB_URL" | sed -E 's#^[a-z]+://[^@]*@([^:/]+).*#\1#')
    DB_WAIT_PORT=$(echo "$DB_URL" | sed -E 's#^[a-z]+://[^@]*@[^:]+:([0-9]+).*#\1#')
fi

# Espera a que la base de datos acepte conexiones (evita que el primer deploy falle
# si Coolify levanta la app antes de que MySQL termine de arrancar).
if [ -n "$DB_WAIT_HOST" ]; then
    echo "Esperando MySQL en ${DB_WAIT_HOST}:${DB_WAIT_PORT}..."
    for i in $(seq 1 30); do
        if (echo > "/dev/tcp/${DB_WAIT_HOST}/${DB_WAIT_PORT}") >/dev/null 2>&1; then
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
