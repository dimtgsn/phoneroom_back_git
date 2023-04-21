<?php

namespace App\Http\Requests\Profile;

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
            'first_name' => 'string|required|max:255|injection',
            'middle_name' => 'string|nullable|max:255|injection',
            'last_name' => 'string|nullable|max:255|injection',
            'email' => 'email|nullable|max:255|injection',
            'phone' => 'string|phone|required|injection',
            'fullAddress' => 'string|nullable|injection',
        ];
    }
}
