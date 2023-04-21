<?php

namespace App\Services\Brand;


use App\Models\Brand;
use App\Utilities\ImageConvertToWebp;
use Illuminate\Support\Facades\Storage;


class Service
{
    public function store($data)
    {
//        $data['image'] = 'storage'.substr($data['image'], 6);
        $imageConvert = new ImageConvertToWebp();

        $data['image'] = 'storage/'.\Storage::disk('public')->put('images/brands', $data['image']);
        if(str_ends_with($data['image'], "jpg") || str_ends_with($data['image'], "png")
            || str_ends_with($data['image'], "gif") || str_ends_with($data['image'], "jpeg")){
            $data['image'] = $imageConvert->convert($data['image'], true);
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
    }

    public function update($data, $brand)
    {
//        if (isset($data['image'])){
//            $data['image'] = 'storage'.substr($data['image'], 6);
//        }
        $imageConvert = new ImageConvertToWebp();
        if(empty($data['image']) !== true){
            $data['image'] = 'storage/'.\Storage::disk('public')->put('images/brands', $data['image']);
            if($brand->image){
                Storage::disk('public')->delete(substr($brand->image, 8));
            }
            if(str_ends_with($data['image'], "jpg") || str_ends_with($data['image'], "png")
                || str_ends_with($data['image'], "gif") || str_ends_with($data['image'], "jpeg")){
                $data['image'] = $imageConvert->convert($data['image'], true);
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
//        if (isset($data['first_name'])){
//            DB::table('users')
//                ->where('users.profile_id', $profile->id)
//                ->update($data['first_name']);
//        }
//
//        if (isset($data['email'])){
//            DB::table('users')
//                ->where('users.profile_id', $profile->id)
//                ->update($data['email']);
//        }
//        unset($data['first_name']);
//        unset($data['email']);
//
//
//        DB::table('profiles')
//            ->where('profiles.id', $profile->id)
//            ->update([
//                'middle_name' => $data['middle_name'] ?? $profile->middle_name,
//                'last_name' => $data['last_name'] ?? $profile->last_name,
//                'phone' => $data['phone'] ?? $profile->phone,
//                'address' => $data['address'] ?? $profile->address,
//            ]);

//        if (isset($data['discount'])){
//            $product->update([
//                'discount' => true,
//            ]);
//            unset($data['discount']);
//        }
//        else{
//            $product->update([
//                'discount' => false,
//            ]);
//        }
//
//        if (isset($data['tags_id'])){
//            $tags = $data['tags_id'];
//            unset($data['tags_id']);
//            $product->tags()->sync($tags);
//        }
//
//        $product->update($data);
//

    }

}