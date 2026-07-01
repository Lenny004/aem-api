<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'El recurso solicitado no existe.'),
        new OA\Property(property: 'errors', type: 'object', nullable: true, example: null),
    ]
)]
class ErrorResponse
{
}
