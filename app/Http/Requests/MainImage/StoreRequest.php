<?php

namespace App\Http\Requests\MainImage;

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
            'paths' => 'array|required',
//            'position' => 'nullable|int|injection',
        ];
    }
}
