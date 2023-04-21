<?php

namespace App\Http\Requests\Message;

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
            'theme' => 'string|required|injection',
            'message' => 'string|required|injection',
        ];
    }
}
