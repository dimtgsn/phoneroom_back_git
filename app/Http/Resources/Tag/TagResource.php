<?php

namespace App\Http\Resources\Tag;

use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
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
            'name' => $this['name'],
            'products' => $this['products'],
//            'name' => $this->name,
//            'products' => $this->products,
        ];
    }
}
