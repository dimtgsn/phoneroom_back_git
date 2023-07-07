<?php

namespace App\Services\Brand;


use App\Models\Brand;
use App\Utilities\ImageConvertToWebp;
use Illuminate\Support\Facades\Storage;


class Service
{
    public function store($data)
    {
        \DB::transaction(function() use ($data) {
            $data['image'] = 'storage/'.\Storage::disk('public')->put('images/brands', $data['image']);
            if(str_ends_with($data['image'], "jpg") || str_ends_with($data['image'], "png")
                || str_ends_with($data['image'], "gif") || str_ends_with($data['image'], "jpeg")){
                $data['image'] = ImageConvertToWebp::convert($data['image'], true);
            }
            if(isset($data['categories_id'])){
                $categories = $data['categories_id'];
                unset($data['categories_id']);
                $brand = Brand::firstOrCreate($data);
                $brand->categories()->attach($categories);
            }
            else{
                Brand::firstOrCreate($data);
            }
        });
    }

    public function update($data, $brand)
    {
        \DB::transaction(function() use ($data, $brand) {
            if(empty($data['image']) !== true){
                $data['image'] = 'storage/'.\Storage::disk('public')->put('images/brands', $data['image']);
                if($brand->image){
                    Storage::disk('public')->delete(substr($brand->image, 8));
                }
                if(str_ends_with($data['image'], "jpg") || str_ends_with($data['image'], "png")
                    || str_ends_with($data['image'], "gif") || str_ends_with($data['image'], "jpeg")){
                    $data['image'] = ImageConvertToWebp::convert($data['image'], true);
                }
            }
            $brand->update([
                'name' => $data['name'] ?? $brand->name,
                'image' => $data['image'] ?? $brand->image,
            ]);

            if (isset($data['categories_id'])){
                $categories = $data['categories_id'];
                unset($data['categories_id']);
                $brand->categories()->sync($categories);
            }
        });
    }

}