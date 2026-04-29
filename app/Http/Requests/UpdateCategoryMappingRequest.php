<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hmrc_category_id' => ['nullable', 'integer', 'exists:hmrc_categories,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
