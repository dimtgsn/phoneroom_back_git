<?php

namespace App\Http\Requests\MyWarehouse;

use Illuminate\Foundation\Http\FormRequest;

class ExportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'ids' => 'array|required',
        ];
    }
}
