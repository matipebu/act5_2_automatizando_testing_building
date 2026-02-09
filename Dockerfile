FROM php:8.2-fpm

# Extensiones para MySQL y SQLite en PHP-FPM
RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copiamos la app
COPY app/ /var/www/html/

# PHP-FPM escucha en 9000
EXPOSE 9000
