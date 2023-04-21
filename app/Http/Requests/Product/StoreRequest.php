<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'name' => 'string|required|max:255|injection',
            'description' => 'string|nullable|injection',
            'price' => 'required|integer',
            'image' => 'required|file|injection',
            'old_price' => 'integer|nullable' ,
            'units_in_stock' => 'required|integer',
            'category_id' => 'required|integer',
            'rating' => 'nullable|numeric',
            'vat' => 'nullable|integer',
            'min_price' => 'nullable|integer',
            'purchase_price' => 'nullable|integer',
            'min_balance' => 'nullable|integer',
            'country' => 'nullable|string',
            'brand_id' => 'required|integer',
            'tags_id' => 'array|nullable',
            'properties' => 'array|nullable',
            'options' => 'array|nullable',
            'published' => '',
            'path_1' => 'nullable|file|injection',
            'path_2' => 'nullable|file|injection',
            'path_3' => 'nullable|file|injection',
            'path_4' => 'nullable|file|injection',
        ];
    }
}
