<?php

namespace App\Http\Requests\Api\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FilterRequest extends FormRequest
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
            'status' => [
                'nullable',
                'string',
                Rule::in(['pending', 'shipped', 'delivered', 'cancelled']),
            ],
            'tags' => 'nullable|array',
        ];
    }

    protected function prepareForValidation(): void
    {
        $tags = $this->input('tags', []);

        if (is_string($tags)) {
            $tags = [$tags];
        }

        $tags = array_filter($tags, fn($tag) => !empty($tag));
        $tags = array_map(fn($tag) => Str::slug($tag, '-'), $tags);

        $this->merge([
            'tags' => $tags,
        ]);
    }
}
