<?php

namespace App\Http\Requests\Category;

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
            'name' => 'string|required|max:255',
            'image' => 'nullable|file',
            'parent_id' => 'nullable|int',
            'brands_id' => 'array|nullable'
        ];
    }
}
