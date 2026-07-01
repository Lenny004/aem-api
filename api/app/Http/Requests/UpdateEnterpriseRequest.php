<?php

namespace App\Http\Requests;

use App\Enums\EnterpriseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida PATCH/PUT /api/v1/enterprises/{id}.
 * No incluye company_id: mover una empresa a otro holding es una operación distinta, no un PATCH genérico.
 */
class UpdateEnterpriseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:150'],
            'doc_number' => [
                'sometimes', 'string', 'max:20',
                Rule::unique('enterprises', 'doc_number')->ignore($this->route('id')),
            ],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'enterprises_status' => ['sometimes', Rule::enum(EnterpriseStatus::class)],
        ];
    }
}
