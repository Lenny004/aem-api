<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Serializa un Company al JSON público de la API.
 * Expone status (valor crudo) y status_label (texto para UI) por separado.
 */
#[OA\Schema(
    schema: 'CompanyResource',
    title: 'Company',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Grupo AEM SV'),
        new OA\Property(property: 'doc_number', type: 'string', example: 'CO-0001'),
        new OA\Property(property: 'email', type: 'string', nullable: true, example: 'contacto@aem.sv'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '2222-3333'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
        new OA\Property(property: 'status_label', type: 'string', example: 'Activo'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'doc_number' => $this->doc_number,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->companys_status->value,
            'status_label' => $this->companys_status->label(),
            'created_at' => $this->created_at,
        ];
    }
}
