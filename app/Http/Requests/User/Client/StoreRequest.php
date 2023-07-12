<?php

namespace App\Http\Requests\User\Client;

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
            'first_name' => 'string|required|max:255|injection',
            'phone' => 'string|phone|required|injection',
            'password' => 'required|string|injection',
            'middle_name' => 'nullable|string|injection',
            'last_name' => 'nullable|string|injection',
            'email' => 'nullable|string|injection',
            'fullAddress' => 'nullable|string|injection',
        ];
    }
}
