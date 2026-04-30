<?php

namespace App\Http\Requests;

use App\Models\HmrcCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHmrcCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var HmrcCategory $hmrcCategory */
        $hmrcCategory = $this->route('hmrcCategory');

        return [
            'code' => ['required', 'string', 'max:255', Rule::unique('hmrc_categories', 'code')->ignore($hmrcCategory)],
            'name' => ['required', 'string', 'max:255'],
            'report_type' => ['required', Rule::in(['self_employment', 'uk_property'])],
            'category_type' => ['required', Rule::in(['income', 'expense'])],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper((string) $this->input('code')),
        ]);
    }
}
