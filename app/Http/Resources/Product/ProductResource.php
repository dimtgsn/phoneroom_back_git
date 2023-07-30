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
            'id' => $this->id ?? $this['id'],
            'slug' => $this->slug ?? $this['slug'],
            'quantity' => $this->quantity ?? 1 ?? $this['quantity'],
            'product_name' => $this->product_name ?? $this->name ?? $this['product_name'] ?? $this['name'],
            'image' => $this->image ?? $this['image'],
            'description' => $this->description ?? $this['description'],
            'price' => $this->price ?? $this['price'],
            'old_price' => $this->old_price ?? $this['old_price'],
            'rating' => (float)$this->rating ?? $this['rating'],
            'option'=> $this->option ?? null ?? $this['option'],
            'published'=> $this->published ?? $this['published'],
            'units_in_stock'=> $this->units_in_stock ?? $this['units_in_stock'],
            'property'=> $this->property->properties_json ?? $this['property']['properties_json'],
            'brand'=> $this->brand->name ?? $this['brand']['name'],
            'brand_image'=> $this->brand->image ?? $this['brand']['image'],
            'tags'=> $this->tags ?? $this['tags'],
            'comments_count' => $this->comments_count ?? $this['comments_count'],
            'comments_count_5' => $this->comments_count_5 ?? $this['comments_count_5'],
            'comments_count_4' => $this->comments_count_4 ?? $this['comments_count_4'],
            'comments_count_3' => $this->comments_count_3 ?? $this['comments_count_3'],
            'comments_count_2' => $this->comments_count_2 ?? $this['comments_count_2'],
            'comments_count_1' => $this->comments_count_1 ?? $this['comments_count_1'],
            'category' => $this->category->name ?? $this['category']['name'],
            'category_slug' => $this->category->slug ?? $this['category']['slug'],
            'category_id' => $this->category->id ?? $this['category']['id'],
            'variants_json' => $this->variants_json ?? null ?? $this['variants_json'],
            'images' => $this->images ?? $this['images'] ?? null ,
        ];
    }
}
