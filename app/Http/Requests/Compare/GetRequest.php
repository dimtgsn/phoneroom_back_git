<?php

namespace App\Http\Requests\Compare;

use Illuminate\Foundation\Http\FormRequest;

class GetRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'category_id' => 'string|required|injection',
        ];
    }
}
