FROM php:8.4-cli

# Dependencias del sistema + extensión pdo_pgsql para hablar con Postgres
RUN apt-get update && apt-get install -y \
        git unzip libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

# Copiamos el binario de Composer desde la imagen oficial (no lo instalamos "a mano")
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
