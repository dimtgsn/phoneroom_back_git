<?php

namespace App\Http\Requests\Logo;

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
            'path' => 'file|nullable',
            'favicon' => 'file|nullable',
        ];
    }
}
