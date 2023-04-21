<?php

namespace App\Http\Requests\Brand;

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
            'image' => 'required|file',
            'categories_id' => 'array|nullable'
        ];
    }
}
