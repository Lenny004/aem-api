<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida el cuerpo de POST /api/v1/companys antes de llegar al controlador.
 * Solo verifica forma y unicidad; la lógica de negocio vive en CompanyService.
 */
class StoreCompanyRequest extends FormRequest
{
    // La autorización real la aplica el middleware auth:api (Fase 6).
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'doc_number' => ['required', 'string', 'max:20', 'unique:companys,doc_number'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}
