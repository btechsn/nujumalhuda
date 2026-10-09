#!/bin/sh
# entrypoint.sh — Point d'entrée pour les conteneurs PHP

set -e

# Attendre que PostgreSQL soit prêt
if [ ! -z "$DB_HOST" ]; then
    echo "⏳ Waiting for PostgreSQL..."
    until nc -z -v -w30 $DB_HOST ${DB_PORT:-5432}; do
        echo "Waiting for database connection..."
        sleep 2
    done
    echo "✅ PostgreSQL is ready!"
fi

# Attendre que Redis soit prêt
if [ ! -z "$REDIS_HOST" ]; then
    echo "⏳ Waiting for Redis..."
    until nc -z -v -w30 $REDIS_HOST ${REDIS_PORT:-6379}; do
        echo "Waiting for Redis connection..."
        sleep 2
    done
    echo "✅ Redis is ready!"
fi

# Installer les dépendances Composer en production (un seul conteneur à la fois)
if [ "$APP_ENV" = "production" ] && [ ! -f vendor/autoload.php ]; then
    echo "📦 Installing Composer dependencies..."
    mkdir -p storage/framework bootstrap/cache
    (
        flock -x 200
        if [ ! -f vendor/autoload.php ]; then
            composer install --no-dev --optimize-autoloader --no-interaction
        fi
    ) 200>storage/framework/.composer-install.lock
fi

# Optimiser en production
if [ "$APP_ENV" = "production" ]; then
    echo "⚡ Optimizing Laravel..."
    if [ -n "$DB_HOST" ]; then
        rm -f bootstrap/cache/config.php
        php artisan config:cache || true
    else
        echo "DB_HOST absent : le cache de configuration n'est pas réécrit."
    fi
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Créer les liens symboliques storage
if [ ! -L "public/storage" ]; then
    echo "🔗 Creating storage link..."
    php artisan storage:link || true
fi

# Corriger les permissions
echo "🔒 Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "🚀 Starting $@..."
exec "$@"
