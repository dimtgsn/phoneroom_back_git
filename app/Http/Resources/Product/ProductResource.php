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
            'rating' => (float)$this->rating,
            'option'=> $this->option ?? null,
            'published'=> $this->published,
            'units_in_stock'=> $this->units_in_stock,
            'property'=> $this->property->properties_json,
            'brand'=> $this->brand->name,
            'brand_image'=> $this->brand->image,
            'tags'=> $this->tags,
            'comments_count' => $this->comments_count,
            'comments_count_5' => $this->comments_count_5,
            'comments_count_4' => $this->comments_count_4,
            'comments_count_3' => $this->comments_count_3,
            'comments_count_2' => $this->comments_count_2,
            'comments_count_1' => $this->comments_count_1,
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
