<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'gateway_id' => ['required', 'exists:gateways,id'],
            'order_id' => ['required', 'unique:transactions']
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
            'gateway_id.required' => 'The gateway ID is required.',
            'gateway_id.exists' => 'The provided gateway ID does not exist.',
            'order_id.required' => 'The order ID is required.',
            'order_id.unique' => 'The order ID already exists.'
        ];
    }
}
