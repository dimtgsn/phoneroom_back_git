<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class MiniProductResource extends JsonResource
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
            'id' => $this['id'] ?? $this->id,
            'slug' => $this['slug'] ?? $this->slug,
            'quantity' => $this['quantity'] ?? 1 ?? $this->quantity,
            'product_name' => $this['product_name'] ?? $this['name'] ?? $this->product_name ?? $this->name,
            'image' => $this['image'] ?? $this->image,
            'description' => $this['description'] ?? $this->description,
            'category' => $this['category']['name'] ?? $this['category'] ?? $this->category->name ?? $this->category,
            'category_id' => $this['category_id'] ?? $this->category_id,
            'price' => $this['price'] ?? $this->price,
            'units_in_stock'=> $this['units_in_stock'] ?? $this->units_in_stock,
            'old_price' => $this['old_price'] ?? $this->old_price,
            'rating' => (float)$this['rating'] ?? $this->rating,
            'published'=> $this['published'] ?? $this->published,
        ];
    }
}
