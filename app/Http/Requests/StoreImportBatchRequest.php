<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreImportBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_id' => ['required', 'integer', 'exists:businesses,id'],
            'file' => [
                'required',
                'file',
                'mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel',
                'max:4096',
            ],
        ];
    }
}
