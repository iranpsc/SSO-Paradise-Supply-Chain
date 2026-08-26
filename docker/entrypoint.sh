#!/bin/sh
set -eu

prepare_writable_dirs() {
    mkdir -p \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/cache/data \
        storage/logs \
        storage/app/public \
        bootstrap/cache
}

run_as_www_data() {
    if [ "$(id -u)" = "0" ]; then
        gosu www-data "$@"
    else
        "$@"
    fi
}

prepare_writable_dirs

if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data storage bootstrap/cache
fi

if [ "${WAIT_FOR_DB:-true}" = "true" ] && [ -n "${DB_HOST:-}" ]; then
    echo "Waiting for database at ${DB_HOST}:${DB_PORT:-3306}..."
    i=0
    until run_as_www_data php -r "
        try {
            new PDO(
                sprintf('mysql:host=%s;port=%s', getenv('DB_HOST'), getenv('DB_PORT') ?: '3306'),
                getenv('DB_USERNAME'),
                getenv('DB_PASSWORD')
            );
            exit(0);
        } catch (Throwable \$e) {
            exit(1);
        }
    "; do
        i=$((i + 1))
        if [ "$i" -ge 60 ]; then
            echo "Database did not become ready in time."
            exit 1
        fi
        sleep 2
    done
fi

if [ "${RUN_PACKAGE_DISCOVER:-true}" = "true" ]; then
    run_as_www_data php artisan package:discover --ansi
fi

if [ "${CONTAINER_ROLE:-app}" = "app" ] && [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    run_as_www_data php artisan migrate --force --no-interaction
fi

if [ ! -f storage/oauth-private.key ] || [ ! -f storage/oauth-public.key ]; then
    run_as_www_data php artisan passport:keys --no-interaction
fi

run_as_www_data php artisan storage:link --force --no-interaction >/dev/null 2>&1 || true

if [ "${APP_ENV:-production}" = "production" ] && [ "${CACHE_CONFIG:-true}" = "true" ]; then
    run_as_www_data php artisan config:cache
    run_as_www_data php artisan route:cache
    run_as_www_data php artisan view:cache
    run_as_www_data php artisan event:cache || true
fi

if [ "$(id -u)" = "0" ]; then
    if [ "${1:-}" = "apache2-foreground" ]; then
        exec "$@"
    fi
    exec gosu www-data "$@"
fi

exec "$@"
