<?php

namespace App\Http\Requests;

use App\Models\Business;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Business $business */
        $business = $this->route('business');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('businesses', 'name')->ignore($business)],
            'ownership_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
