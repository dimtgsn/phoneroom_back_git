<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public static $wrap = '';
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
//            'variantId' => $this->variantId,
            'slug' => $this->slug,
            'quantity' => $this->quantity ?? 1,
            'product_name' => $this->product_name ?? $this->name,
//            'variants' =>  $this->variants ?? null,
            'image' => $this->image,
            'description' => $this->description,
            'price' => $this->price,
            'old_price' => $this->old_price,
            'rating' => $this->rating,
            'option'=> $this->option ?? null,
            'published'=> $this->published,
            'property'=> $this->property->properties_json,
            'brand'=> $this->brand->name,
            'brand_image'=> $this->brand->image,
            'tags'=> $this->tags,
            'category' => $this->category->name,
            'category_slug' => $this->category->slug,
            'category_id' => $this->category->id,
            'variants_json' => $this->variants_json ?? null,
            'images' => $this->images ?? null,
//            'brand_id' => $this->brand_id,
//            'category_id' => $this->category_id,
        ];
    }
}
