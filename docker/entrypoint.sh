#!/bin/bash

# Sin "set -e": un fallo de conexión/migración NO debe tirar abajo el container.
# Si eso pasa, Coolify lo reinicia en loop y nunca queda en estado "running" el
# tiempo suficiente para dar acceso a terminal — quedás sin forma de debuggear.
# Preferimos: loguear el problema y arrancar igual nginx+php-fpm (la app puede
# devolver 500 en rutas que usan la DB, pero el container queda vivo e inspeccionable).

# Si se configuró DB_URL (ej. Coolify) en vez de las variables sueltas, sacamos
# host/puerto de ahí para poder esperar a que la base de datos esté lista.
DB_WAIT_HOST="$DB_HOST"
DB_WAIT_PORT="${DB_PORT:-3306}"
if [ -z "$DB_WAIT_HOST" ] && [ -n "$DB_URL" ]; then
    # mysql://user:pass@host:port/db -> host y puerto
    DB_WAIT_HOST=$(echo "$DB_URL" | sed -E 's#^[a-z]+://[^@]*@([^:/]+).*#\1#')
    DB_WAIT_PORT=$(echo "$DB_URL" | sed -E 's#^[a-z]+://[^@]*@[^:]+:([0-9]+).*#\1#')
fi

DB_READY=false
if [ -n "$DB_WAIT_HOST" ]; then
    echo "Esperando MySQL en ${DB_WAIT_HOST}:${DB_WAIT_PORT}..."
    for i in $(seq 1 30); do
        if (echo > "/dev/tcp/${DB_WAIT_HOST}/${DB_WAIT_PORT}") >/dev/null 2>&1; then
            echo "MySQL disponible."
            DB_READY=true
            break
        fi
        sleep 2
    done
    if [ "$DB_READY" != "true" ]; then
        echo "ADVERTENCIA: no se pudo conectar a ${DB_WAIT_HOST}:${DB_WAIT_PORT} tras 60s." >&2
        echo "Revisá DB_URL/DB_HOST, que la base de datos esté arriba, y que este container y el de MySQL estén en la misma red." >&2
    fi
else
    echo "ADVERTENCIA: no hay DB_HOST ni DB_URL configurados; no se puede esperar a la base de datos." >&2
fi

php artisan config:cache || echo "ADVERTENCIA: config:cache falló." >&2
php artisan route:cache || echo "ADVERTENCIA: route:cache falló." >&2
php artisan view:cache || echo "ADVERTENCIA: view:cache falló." >&2

if [ "$DB_READY" = "true" ] && [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force || echo "ADVERTENCIA: migrate falló. La app puede fallar en rutas que usan la base de datos hasta que se resuelva la conexión." >&2
elif [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Salteando migrate: la base de datos no respondió durante la espera." >&2
fi

exec "$@"
