<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida POST /api/v1/branchs.
 * municipality_codigo se cruza con config/municipalities.php (misma regla que el CHECK de Postgres).
 */
class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enterprise_id' => ['required', 'integer', 'exists:enterprises,id'],
            'name' => ['required', 'string', 'max:150'],
            'address' => ['required', 'string', 'max:255'],
            'municipality_codigo' => ['required', 'string', Rule::in(config('municipalities.codes'))],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}
