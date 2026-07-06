# syntax=docker/dockerfile:1

############################################
# Etapa 1: dependencias de PHP (composer)
############################################
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
# --ignore-platform-reqs: esta imagen de composer no trae todas las extensiones
# (ej. intl, gd) que sí van a estar presentes en la imagen final de runtime (etapa "app").
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

############################################
# Etapa 2: assets de frontend (Vite + Tailwind)
############################################
FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json pnpm-lock.yaml ./
# npm install en vez de corepack: corepack necesita resolver la version "latest"
# contra el registry en cada build, lo cual es fragil (fallo real durante el
# desarrollo de este Dockerfile) y no reproducible. npm instala una version fija.
RUN npm install -g pnpm@10 \
    && pnpm install --frozen-lockfile

COPY resources ./resources
COPY vite.config.js ./
COPY public ./public

RUN pnpm build

############################################
# Etapa 3: runtime (PHP-FPM + Nginx + Supervisor)
############################################
FROM php:8.2-fpm-alpine AS app

# Libs de desarrollo (temporales, para compilar extensiones) + libs de runtime (se quedan) + herramientas.
RUN apk add --no-cache --virtual .build-deps \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        oniguruma-dev \
        icu-dev \
    && apk add --no-cache \
        nginx \
        supervisor \
        bash \
        mysql-client \
        libpng \
        libjpeg-turbo \
        freetype \
        libzip \
        icu-libs \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        bcmath \
        exif \
        pcntl \
        intl \
        opcache \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

COPY --from=vendor /app ./
COPY --from=assets /app/public/build ./public/build

RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache \
        storage/logs storage/app/tickets bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache-recommended.ini
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
