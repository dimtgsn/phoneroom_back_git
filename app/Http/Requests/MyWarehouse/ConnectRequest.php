<?php

namespace App\Http\Requests\MyWarehouse;

use Illuminate\Foundation\Http\FormRequest;

class ConnectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'login' => 'string|required|injection',
            'password' => 'string|required|injection',
        ];
    }
}
