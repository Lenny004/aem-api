<?php

namespace App\Http\Requests;

use App\Enums\BranchStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:150'],
            'address' => ['sometimes', 'string', 'max:255'],
            'municipality_codigo' => ['sometimes', 'string', Rule::in(config('municipalities.codes'))],
            'phone' => ['nullable', 'string', 'max:20'],
            'branchs_status' => ['sometimes', Rule::enum(BranchStatus::class)],
        ];
    }
}
