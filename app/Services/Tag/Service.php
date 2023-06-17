<?php

namespace App\Services\Tag;


use App\Models\Brand;
use App\Models\Tag;
use App\Utilities\ImageConvertToWebp;


class Service
{
    public function store($data)
    {
        $imageConvert = new ImageConvertToWebp();
        \DB::transaction(function() use ($data, $imageConvert) {
            $data['image'] = 'storage'.\Storage::disk('public')->put('images/tags', $data['image']);
            if(str_ends_with($data['image'], "jpg") || str_ends_with($data['image'], "png")
                || str_ends_with($data['image'], "gif") || str_ends_with($data['image'], "jpeg")){
                $data['image'] = $imageConvert->convert($data['image'], true);
            }
            if(isset($data['products_id'])){
                $products = $data['products_id'];
                unset($data['products_id']);
                $tag = Tag::firstOrCreate($data);
                $tag->products()->attach($products);
            }
            else{
                Tag::firstOrCreate($data);
            }
        });
    }

    public function update($data, $tag)
    {
        $imageConvert = new ImageConvertToWebp();
        \DB::transaction(function() use ($data, $imageConvert) {
            if(empty($data['image']) !== true){
                $data['image'] = 'storage'.\Storage::disk('public')->put('images/tags', $data['image']);
                if($tag->image){
                    \Storage::disk('public')->delete(substr($tag->image, 8));
                }
                if(str_ends_with($data['image'], "jpg") || str_ends_with($data['image'], "png")
                    || str_ends_with($data['image'], "gif") || str_ends_with($data['image'], "jpeg")){
                    $data['image'] = $imageConvert->convert($data['image'], true);
                }
            }
            $tag->update([
                'name' => $data['name'] ?? $tag->name,
                'image' => $data['image'] ?? $tag->image,
            ]);

            if (isset($data['products_id'])){
                $products = $data['products_id'];
                unset($data['products_id']);
                $tag->products()->sync($products);
            }
        });
    }

}