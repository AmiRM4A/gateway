<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGatewayRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_path' => ['required', 'string'],
            'api_key' => ['required', 'string', 'unique:gateways'],
            'description' => ['nullable', 'string']
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'service_path.required' => 'The API name is required.',
            'service_path.string' => 'The API name must be a string.',
            'api_key.required' => 'The API key is required.',
            'api_key.string' => 'The API key must be a string.',
            'api_key.unique' => 'The API key already exists.',
            'description.string' => 'The description must be a string.'
        ];
    }
}
