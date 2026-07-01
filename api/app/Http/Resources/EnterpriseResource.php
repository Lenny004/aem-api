<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Serializa una Enterprise al JSON público de la API.
 * La clave company solo aparece si el controlador cargó la relación con with()/load().
 */
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
