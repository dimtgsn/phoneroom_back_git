<?php

namespace App\Http\Requests\Tag;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'string|required|max:255|injection',
            'image' => 'nullable|file|injection',
            'products_id' => 'array|nullable'
        ];
    }
}
