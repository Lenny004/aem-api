# API de Gestión de Infraestructura Comercial (AEM)

API REST en Laravel 12 + PostgreSQL, arquitectura Controller-Service-Repository.

## Requisitos

- Docker Desktop (con Docker Compose)

## Levantar el proyecto

\`\`\`bash
docker compose up -d --build
docker compose exec api-server php artisan migrate --force
\`\`\`

La API queda disponible en http://localhost:8000

## Estructura del repositorio

- `api/` — proyecto Laravel (código de la aplicación)
- `docs/` — documento de requerimientos de la prueba técnica
- `fases/` — plan de desarrollo, dividido por fase
- `plan.md` — índice general del plan de desarrollo