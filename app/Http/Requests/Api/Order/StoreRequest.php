<?php

namespace App\Http\Requests\Api\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_number' => 'required|string:max:255|unique:orders',
            //'total_amount' => 'required|numeric|min:0.01|max:9999.99',
            'tags' => 'array|nullable',
            'tags.*' => 'string|max:255',
            'items' => 'required|array',
            'items.*.product_name' => 'required|string|max:255', // Validate the 'name' attribute of each product
            'items.*.price' => 'required|numeric|min:0.01|max:9999.99', // Validate the 'price' attribute of each product
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
