<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Serializa una Enterprise al JSON público de la API.
 * La clave company solo aparece si el controlador cargó la relación con with()/load().
 */
#[OA\Schema(
    schema: 'EnterpriseResource',
    title: 'Enterprise',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'company_id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Enterprise Asociada SV'),
        new OA\Property(property: 'doc_number', type: 'string', example: 'EN-0001'),
        new OA\Property(property: 'email', type: 'string', nullable: true),
        new OA\Property(property: 'phone', type: 'string', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
        new OA\Property(property: 'status_label', type: 'string', example: 'Activo'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'company', ref: '#/components/schemas/CompanyResource', nullable: true),
    ]
)]
class EnterpriseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name' => $this->name,
            'doc_number' => $this->doc_number,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->enterprises_status->value,
            'status_label' => $this->enterprises_status->label(),
            'created_at' => $this->created_at,
            // whenLoaded evita N+1: no dispara consultas extra si nadie pidió el padre.
            'company' => CompanyResource::make($this->whenLoaded('company')),
        ];
    }
}
