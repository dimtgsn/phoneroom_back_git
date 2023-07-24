<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'name' => 'string|nullable|max:255|injection',
            'description' => 'string|nullable|injection',
            'price' => 'nullable|integer|gt:0',
            'image' => 'nullable|file|injection',
            'old_price' => 'integer|nullable|gte:0' ,
            'units_in_stock' => 'nullable|integer|gte:0',
            'category_id' => 'nullable|integer|gt:0',
            'brand_id' => 'nullable|integer|gt:0',
            'vat' => 'nullable|integer',
            'min_price' => 'nullable|integer|gte:0',
            'purchase_price' => 'nullable|integer|gte:0',
            'min_balance' => 'nullable|integer|gte:0',
            'country' => 'nullable|string',
            'tags_id' => 'array|nullable',
            'rating' => 'numeric|nullable|gte:0.0',
            'properties' => 'array|nullable',
            'options' => 'array|nullable',
            'published' => '',
            'path_1' => 'nullable|file|injection',
            'path_2' => 'nullable|file|injection',
            'path_3' => 'nullable|file|injection',
            'path_4' => 'nullable|file|injection',
            'del_path_1' => 'nullable|injection',
            'del_path_2' => 'nullable|injection',
            'del_path_3' => 'nullable|injection',
            'del_path_4' => 'nullable|injection',
        ];
    }
}
