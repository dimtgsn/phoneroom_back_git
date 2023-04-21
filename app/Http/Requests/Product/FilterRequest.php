<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'string|injection',
            'description' => 'string|injection',
            'price' => 'integer',
            'image' => 'string|injection',
            'amount' => 'integer',
            'category_id' => '',
            'tags_id' => '',
            'discount' => '',

            'page' => '', // какая страница 1, 2...
            'per_page' => '', // сколько товаров на странице
        ];
    }
}
