<?php

namespace App\Http\Resources\Category;

use App\Http\Resources\Brand\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'brands' => $this->brands,
//            'brand_id' => $this->whenPivotLoaded('brand_category', function () {
//                return $this->pivot->brand_id;
//            }),
//            'brands' => Brand::where('id', )->select('id', 'slug', 'name')->orderBy('id')->get(),
//            'products' => $this->products,
        ];
    }
}
