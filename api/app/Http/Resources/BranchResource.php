<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Serializa una Branch al JSON público de la API.
 * enterprise (y company anidado dentro) solo se incluyen si fueron cargados explícitamente.
 */
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
