# API de Gestión de Infraestructura Comercial (AEM)

API REST en **Laravel 12 + PostgreSQL 16**, con arquitectura estricta **Controller → Service → Repository**,
autenticación **JWT**, manejo global de excepciones, documentación **OpenAPI/Swagger** interactiva y
suite de pruebas automatizadas contra una base de datos real.

Modela una jerarquía de negocio de 3 niveles:

```
Company (Holding / Consorcio)
  └── Enterprise (Empresa Asociada, pertenece a 1 Company)
        └── Branch (Sucursal, pertenece a 1 Enterprise)
```

---

## Requisitos

- Docker Desktop (incluye Docker Compose) — nada más. No se necesita PHP, Composer ni Postgres instalados en el host.

## Puesta en marcha (un solo comando)

```bash
docker compose up -d --build
```

Al arrancar, el contenedor `api-server` corre automáticamente (ver `api/docker/entrypoint.sh`): genera `.env`/`APP_KEY`/`JWT_SECRET` si faltan, instala dependencias si falta `vendor/`, corre migraciones, siembra un usuario de prueba y genera el JSON de OpenAPI — sin pasos manuales adicionales.

La API queda disponible en **[http://localhost:8000](http://localhost:8000)**.

Verifica que ambos servicios estén saludables:

```bash
docker compose ps
```

## Autenticación (JWT)

Todos los endpoints de negocio requieren un token Bearer, excepto `POST /v1/auth/login`.

Usuario semilla creado automáticamente al arrancar:


| Email            | Password      |
| ---------------- | ------------- |
| `admin@aem.test` | `password123` |


Obtener un token:

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@aem.test","password":"password123"}'
```

Respuesta:

```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

Usar el token en cualquier endpoint protegido:

```bash
curl http://localhost:8000/api/v1/companys \
  -H "Authorization: Bearer <access_token>"
```

Otros endpoints de sesión: `GET /v1/auth/me`, `POST /v1/auth/refresh`, `POST /v1/auth/logout`.

## Documentación interactiva (Swagger / OpenAPI)

```
http://localhost:8000/docs
```

Documentado con atributos nativos de PHP 8 (`#[OA\...]`, no anotaciones DocBlock) sobre cada controlador y `Resource`. Incluye los 3 recursos de negocio + Auth, con parámetros de consulta, request bodies y **todos** los códigos HTTP reales (200/201/204/401/404/409/422). Para probar un endpoint protegido desde la UI: botón **Authorize** → pega el token obtenido arriba (sin la palabra `Bearer`, Swagger UI la agrega sola).

## Endpoints principales

Prefijo común: `/api/v1`. Todos requieren `Authorization: Bearer <token>` salvo `auth/login`.


| Recurso       | Endpoints                                                                                                                                                                                                                                                           |
| ------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `companys`    | `GET /companys` (filtros: `companys_status`, `per_page`) · `GET /companys/{id}` · `POST /companys` · `PUT /companys/{id}` · `DELETE /companys/{id}` (soft delete) · `PATCH /companys/{id}/activate` · `PATCH /companys/{id}/deactivate`                             |
| `enterprises` | Igual patrón que `companys`, más filtro `company_id`. `POST` valida que la `company` padre exista (422 si no) y esté activa (409 si no)                                                                                                                             |
| `branchs`     | Igual patrón, más **filtrado combinado** `enterprises_id` + `municipality_codigo` (requisito explícito de la prueba). `POST` valida que la `enterprise` padre exista y esté activa, y que `municipality_codigo` esté en el catálogo de 44 municipios de El Salvador |


**Desactivar vs. eliminar** (no son lo mismo, ambos están implementados):

- `PATCH .../activate` / `.../deactivate` → cambia el campo `*_status` (`active`/`inactive`/`suspended` en `branchs`). Reversible, el registro sigue existiendo.
- `DELETE .../{id}` → soft delete (`deleted_at`), no borra la fila. Se **rechaza con 409** si la entidad tiene hijos activos (ej. borrar una `company` con `enterprises` activas debajo).

## Pruebas automatizadas

```bash
docker compose exec api-server php artisan test
```

18 tests (Feature + Unit) contra una base PostgreSQL de pruebas real (no SQLite en memoria — el esquema usa `CHECK` constraints específicos de Postgres que SQLite no soporta). Cubren happy paths (201), payload incompleto (422), ID inexistente (404), la regla de negocio "enterprise/branch verifica que su padre exista y esté activo" (probada tanto por HTTP como directamente contra la capa de `Service`), y el filtrado combinado de `branchs`.

Si es la primera vez que corres los tests, crea antes la base de datos de pruebas:

```bash
docker exec aem-db-postgres createdb -U aem_user aem_db_testing
```

## Arquitectura

Patrón **Controller → Service → Repository** aplicado de forma estricta: el Controller nunca toca la base de datos directamente, el Service concentra toda la lógica y reglas de negocio, el Repository aísla por completo el motor de datos (Eloquent) detrás de una interfaz.

```
app/
├── Http/
│   ├── Controllers/     # Solo valida forma de entrada (vía FormRequest) y delega
│   ├── Requests/        # FormRequests: reglas de validación por endpoint
│   └── Resources/       # Forma exacta del JSON de salida
├── Services/            # Lógica y reglas de negocio (ej. "el padre debe existir y estar activo")
├── Repositories/
│   ├── Contracts/       # Interfaces — el Service depende de estas, no de Eloquent directamente
│   └── ...Repository    # Implementación real con Eloquent
├── Models/               # Entidades Eloquent (Company, Enterprise, Branch, User)
├── Exceptions/Domain/    # Excepciones de negocio con código HTTP propio (404/409)
└── OpenApi/              # Atributos globales y esquemas reutilizables de Swagger
```

## Decisiones de diseño relevantes

- **No existía un script DDL provisto,** el esquema (`docs/ddl/schema.sql`) se diseñó desde cero siguiendo los nombres y campos que la guía menciona explícitamente.
- `ON DELETE RESTRICT` **+** `ON UPDATE CASCADE` en ambas FKs (`companys→enterprises`, `enterprises→branchs`): una entidad con hijos no puede eliminarse en duro por error; la "eliminación" real del negocio es el soft delete (`deleted_at`), que además queda bloqueado (409) si hay hijos **activos**.
- `*_status` **(enum) y** `deleted_at` **(soft delete) son mecanismos distintos y complementarios**, no alternativos: el primero es un estado de negocio reversible por el usuario; el segundo es la baja lógica del registro.
- **Índices** en las 3 FKs, en cada campo `*_status` y en los campos operativos de alta frecuencia de filtrado (`doc_number`, `municipality_codigo`, y el índice compuesto `(enterprise_id, municipality_codigo)` para el filtro combinado de `branchs`).
- **JWT** (`tymon/jwt-auth`) sobre API Key: estándar, sin estado en el servidor, y explícitamente válido según la guía.
- **Manejo global de excepciones** en `bootstrap/app.php` (`withExceptions`): toda excepción — de negocio o del framework — responde `{ "message": string, "errors": object|null }` con el código HTTP correcto; nunca se expone un stack trace, ni el nombre de una clase interna, ni SQL crudo.
- **Swagger con atributos PHP 8** (`#[OA\...]`) en vez de anotaciones DocBlock: es la migración recomendada por `darkaonline/l5-swagger` desde su v10, con errores de sintaxis detectados por el propio parser de PHP.

## Variables de entorno

Ver `api/.env.example` para la lista completa y comentada. Las más relevantes:


| Variable                     | Rol                                                                                                         |
| ---------------------------- | ----------------------------------------------------------------------------------------------------------- |
| `DB_*`                       | Conexión a PostgreSQL — `DB_HOST=db-postgres` (nombre del servicio en `docker-compose.yml`, no `localhost`) |
| `JWT_SECRET`                 | Firma de los tokens JWT — se genera solo en el primer arranque si falta                                     |
| `APP_DEBUG`                  | `false` por defecto en `.env.example` (obligatorio en entrega: nunca exponer detalles internos)             |
| `L5_SWAGGER_GENERATE_ALWAYS` | `false` por defecto — el JSON de Swagger se genera una vez al arrancar el contenedor, no en cada request    |


## Estructura del repositorio

```
aem-api/
├── api/                  # Proyecto Laravel completo (código de la aplicación)
│   ├── app/
│   ├── docker/entrypoint.sh
│   ├── Dockerfile
│   └── ...
├── docs/ddl/             # Esquema SQL de referencia (schema.sql, seed.sql)
├── docker-compose.yml    # api-server + db-postgres, red interna, volumen nombrado, healthcheck
└── README.md
```

