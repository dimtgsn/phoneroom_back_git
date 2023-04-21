<?php

namespace App\Http\Requests\Tag;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'string|nullable|max:255|injection',
            'image' => 'nullable|file|injection',
            'products_id' => 'array|nullable'
        ];
    }
}
