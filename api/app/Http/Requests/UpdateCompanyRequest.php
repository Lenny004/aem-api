<?php

namespace App\Http\Requests;

use App\Enums\CompanyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida PATCH/PUT /api/v1/companys/{id}.
 * Usa route('id') (valor crudo) en lugar de Route Model Binding — ver CompanyController.
 */
class UpdateCompanyRequest extends FormRequest
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
                // Ignora el registro actual para permitir reenviar el mismo doc_number sin error.
                Rule::unique('companys', 'doc_number')->ignore($this->route('id')),
            ],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'companys_status' => ['sometimes', Rule::enum(CompanyStatus::class)],
        ];
    }
}
