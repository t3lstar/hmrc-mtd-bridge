<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBusinessFreeAgentCredentialsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'freeagent_client_id' => ['nullable', 'string', 'max:255', 'required_with:freeagent_client_secret,freeagent_refresh_token,freeagent_access_token'],
            'freeagent_client_secret' => ['nullable', 'string', 'required_with:freeagent_client_id,freeagent_refresh_token,freeagent_access_token'],
            'freeagent_refresh_token' => ['nullable', 'string', 'required_with:freeagent_client_id,freeagent_client_secret,freeagent_access_token'],
            'freeagent_access_token' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach ([
            'freeagent_client_id',
            'freeagent_client_secret',
            'freeagent_refresh_token',
            'freeagent_access_token',
        ] as $field) {
            $value = $this->input($field);
            $normalized[$field] = is_string($value) ? trim($value) : $value;

            if ($normalized[$field] === '') {
                $normalized[$field] = null;
            }
        }

        $this->merge($normalized);
    }
}
