#!/bin/sh
set -e

cd /var/www/html

# 1. Si no existe .env (clon nuevo, o bind mount sin el archivo), se crea desde la plantilla.
if [ ! -f .env ]; then
    echo "[entrypoint] .env no encontrado — copiando desde .env.example"
    cp .env.example .env
fi

# 2. Si vendor/ no existe o quedó incompleto (bind mount tapó el vendor/ del build), se reinstala.
#    Idempotente: si ya existe, este paso no hace nada costoso (composer detecta que no hay cambios).
if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] vendor/ no encontrado — corriendo composer install"
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# 3. APP_KEY vacío → Laravel no puede cifrar sesiones/cookies.
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "[entrypoint] Generando APP_KEY"
    php artisan key:generate --force
fi

# 4. JWT_SECRET vacío → el guard 'api' (Fase 6) no puede firmar tokens.
if ! grep -q "^JWT_SECRET=.\+" .env; then
    echo "[entrypoint] Generando JWT_SECRET"
    php artisan jwt:secret --force
fi

# 5. Migraciones: db-postgres ya está healthy en este punto (depends_on de docker-compose.yml).
#    --force silencia la confirmación interactiva; es seguro correrlo en cada arranque
#    porque Laravel solo aplica las migraciones pendientes.
echo "[entrypoint] Ejecutando migraciones pendientes"
php artisan migrate --force

# 6. Usuario semilla para poder loguearse de inmediato (DatabaseSeeder es idempotente).
echo "[entrypoint] Sembrando datos iniciales"
php artisan db:seed --force

# 7. Genera el JSON de Swagger una vez al desplegar — necesario porque en el .env.example
#    de entrega L5_SWAGGER_GENERATE_ALWAYS=false (Fase 8, pendiente resuelto aquí).
echo "[entrypoint] Generando documentación OpenAPI"
php artisan l5-swagger:generate

echo "[entrypoint] Listo — iniciando servidor"
exec php artisan serve --host=0.0.0.0 --port=8000
