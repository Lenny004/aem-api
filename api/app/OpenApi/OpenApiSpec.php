<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'AEM API — Gestión de Infraestructura Comercial',
    description: 'API REST para administrar Holdings (companys), Empresas Asociadas (enterprises) y Sucursales (branchs), con jerarquía estricta de 3 niveles y arquitectura Controller-Service-Repository.'
)]
#[OA\Server(url: '/api', description: 'Prefijo aplicado automáticamente por Laravel a routes/api.php')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Token obtenido en POST /v1/auth/login. Enviar como header: Authorization: Bearer {token}'
)]
class OpenApiSpec
{
    // Clase intencionalmente vacía: solo existe para portar los atributos de arriba.
}
