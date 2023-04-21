<?php

namespace App\Http\Requests\MainImage;

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
            'positions' => 'array|required',
            'paths' => 'array|nullable',
        ];
    }
}
