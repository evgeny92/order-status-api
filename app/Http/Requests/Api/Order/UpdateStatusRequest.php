<?php

namespace App\Http\Requests\Api\Order;

use App\Enum\Order\OrderStatus;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStatusRequest extends FormRequest
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
            'id' => 'required|integer|exists:orders,id',
            'status' => [
                'required',
                'string',
                Rule::enum(OrderStatus::class),
            ],
            'tags' => 'array|nullable',
            'tags.*' => 'string|max:255',
        ];
    }
}
