<?php

namespace App\Http\Requests\Basket;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'string|required|injection',
//            'user_id' => 'string|nullable',
            'quantity' => 'string|nullable|injection',
        ];
    }
}
