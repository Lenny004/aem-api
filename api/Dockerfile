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

# sed -i 's/\r$//' es una red de seguridad: si el archivo se editó en Windows y quedó
# con finales de línea CRLF, "#!/bin/sh" fallaría dentro del contenedor Linux con un
# error críptico de "bad interpreter". Se normaliza a LF antes de darle permiso de ejecución.
RUN sed -i 's/\r$//' docker/entrypoint.sh && chmod +x docker/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["docker/entrypoint.sh"]
