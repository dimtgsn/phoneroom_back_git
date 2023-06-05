<?php

namespace App\Http\Requests\User;

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
            'middle_name' => 'string|nullable|max:255|injection',
            'last_name' => 'string|nullable|max:255|injection',
            'phone' => 'string|phone|required|injection',
            'email' => 'string|email|nullable|max:255|injection',
            'password' => 'nullable|string|min:8|confirmed|injection',
            'fullAddress' => 'string|nullable|injection',
//            'street' => 'string|nullable|max:255',
//            'house' => 'string|nullable|max:255',
        ];
    }
}
