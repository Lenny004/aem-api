<?php

namespace App\Http\Requests;

use App\Enums\CompanyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
                Rule::unique('companys', 'doc_number')->ignore($this->route('id')),
            ],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'companys_status' => ['sometimes', Rule::enum(CompanyStatus::class)],
        ];
    }
}
