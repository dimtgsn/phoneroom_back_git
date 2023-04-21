<?php

namespace App\Http\Requests\Compare;

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
            'category_id' => 'string|required|injection',
        ];
    }
}
