<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Serializa una Branch al JSON público de la API.
 * enterprise (y company anidado dentro) solo se incluyen si fueron cargados explícitamente.
 */
#[OA\Schema(
    schema: 'BranchResource',
    title: 'Branch',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'enterprise_id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Sucursal Centro'),
        new OA\Property(property: 'address', type: 'string', example: 'Calle Arce 123, San Salvador'),
        new OA\Property(property: 'municipality_codigo', type: 'string', example: 'SS-01'),
        new OA\Property(property: 'phone', type: 'string', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'suspended'], example: 'active'),
        new OA\Property(property: 'status_label', type: 'string', example: 'Activo'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'enterprise', ref: '#/components/schemas/EnterpriseResource', nullable: true),
    ]
)]
class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'enterprise_id' => $this->enterprise_id,
            'name' => $this->name,
            'address' => $this->address,
            'municipality_codigo' => $this->municipality_codigo,
            'phone' => $this->phone,
            'status' => $this->branchs_status->value,
            'status_label' => $this->branchs_status->label(),
            'created_at' => $this->created_at,
            'enterprise' => EnterpriseResource::make($this->whenLoaded('enterprise')),
        ];
    }
}
