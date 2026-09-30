# Dockerfile — PHP 8.3 FPM avec extensions pour Laravel
FROM php:8.3-fpm-alpine

# Variables d'environnement
ENV PHPIZE_DEPS \
    autoconf \
    dpkg-dev \
    file \
    g++ \
    gcc \
    libc-dev \
    make \
    pkgconf \
    re2c

# Installation des dépendances système
RUN apk add --no-cache \
    curl \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    postgresql-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        intl \
        opcache \
        pcntl \
        bcmath \
        exif \
        mbstring \
        xml \
        soap

# Installation de Redis extension
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# Installation de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configuration PHP
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Création de l'utilisateur www-data avec UID/GID fixes
RUN addgroup -g 1000 www-data || true \
    && adduser -D -u 1000 -G www-data www-data || true

# Répertoire de travail
WORKDIR /var/www/backend

# Permissions
RUN chown -R www-data:www-data /var/www

# Script d'entrée personnalisé
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER www-data

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
