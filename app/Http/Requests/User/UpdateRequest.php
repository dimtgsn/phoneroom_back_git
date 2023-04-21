<?php

namespace App\Http\Requests\User;

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
            'first_name' => 'string|nullable|injection',
            'middle_name' => 'string|nullable|injection',
            'last_name' => 'string|nullable|injection',
            'email' => 'email|nullable|injection',
            'phone' => 'string|phone|size:11|nullable|injection',
            'fullAddress' => 'string|nullable|injection',
        ];
    }
}
