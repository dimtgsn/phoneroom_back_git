<?php

namespace App\Http\Requests\Category;

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
            'name' => 'string|nullable|max:255',
            'image' => 'nullable|file',
            'parent_id' => 'nullable|string',
            'brands_id' => 'array|nullable'
        ];
    }
}
