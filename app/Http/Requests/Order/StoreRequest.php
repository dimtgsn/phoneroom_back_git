<?php

namespace App\Http\Requests\Order;

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
            'details' => 'string|required|injection',
            'user_id' => 'integer|required|injection',
            'total' => 'integer|required|injection',
            'ship_address' => 'string|required|injection',
            'Zip' => 'string|required|injection|digits:6',
        ];
    }
}
