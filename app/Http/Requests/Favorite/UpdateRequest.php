<?php

namespace App\Http\Requests\Favorite;

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
            'product_id' => 'integer|required|injection',
//            'user_id' => 'integer|nullable',
        ];
    }
}
