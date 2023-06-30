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
            'id' => $this['id'],
            'slug' => $this['slug'],
            'quantity' => $this['quantity'] ?? 1,
            'product_name' => $this['product_name'] ?? $this['name'],
            'image' => $this['image'],
            'description' => $this['description'],
            'category' => $this['category']['name'] ?? $this['category'],
            'price' => $this['price'],
            'units_in_stock'=> $this['units_in_stock'],
            'old_price' => $this['old_price'],
            'rating' => $this['rating'],
            'published'=> $this['published'],
        ];
    }
}
