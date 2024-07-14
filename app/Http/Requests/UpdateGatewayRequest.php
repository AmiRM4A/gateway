<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGatewayRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_path' => ['string'],
            'api_key' => ['string', 'exists:gateways'],
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
            'service_path.string' => 'The gateway name must be a string.',
            'api_key.required' => 'The API key is required.',
            'api_key.string' => 'The API key must be a string.',
            'api_key.exists' => 'The given API key does NOT exists!',
            'description.string' => 'The description must be a string.'
        ];
    }
}
