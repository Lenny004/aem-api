<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'company' => CompanyResource::make($this->whenLoaded('company')),
        ];
    }
}
