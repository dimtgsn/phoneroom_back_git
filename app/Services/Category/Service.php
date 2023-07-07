<?php

namespace App\Services\Category;


use App\Models\Category;
use App\Models\MyWarehouse;
use App\Utilities\ImageConvertToWebp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Service
{
    public function store($data)
    {
        DB::transaction(function() use ($data) {
            if (isset($data['image'])){
                $data['image'] = 'storage/'.\Storage::disk('public')->put('images/categories', $data['image']);
                if(str_ends_with($data['image'], "jpg") || str_ends_with($data['image'], "png")
                    || str_ends_with($data['image'], "gif") || str_ends_with($data['image'], "jpeg")){
                    $data['image'] = ImageConvertToWebp::convert($data['image'], true);
                }
            }
            if (isset($data['brands_id'])){
                $brands = $data['brands_id'];
                unset($data['brands_id']);
                $categoty = Category::firstOrCreate($data);
                $categoty->brands()->attach($brands);
            }
            else{
                Category::firstOrCreate($data);
            }
        });


    }

    public function update($data, $category)
    {
        DB::transaction(function() use ($data, $category) {

            if(empty($data['image']) !== true){
                $data['image'] = 'storage/'.Storage::disk('public')->put('images/categories', $data['image']);
                if($category->image){
                    Storage::disk('public')->delete(substr($category->image, 8));
                }
                if(str_ends_with($data['image'], "jpg") || str_ends_with($data['image'], "png")
                    || str_ends_with($data['image'], "gif") || str_ends_with($data['image'], "jpeg")){
                    $data['image'] = ImageConvertToWebp::convert($data['image'], true);
                }
            }
            $category->update([
                'name' => $data['name'] ?? $category->name,
                'image' => $data['image'] ?? $category->image,
                'parent_id' => $data['parent_id'] ?? $category->parent_id
            ]);

            if (isset($data['brands_id'])){
                $brands = $data['brands_id'];
                unset($data['brands_id']);
                $category->brands()->sync($brands);
            }

            if ($category->my_warehouse_id){
                $myWarehouse = MyWarehouse::select('token')->first();

                Http::withToken($myWarehouse->token)
                    ->withHeaders([
                        "Content-Type" => "application/json"
                    ])
                    ->put('https://online.moysklad.ru/api/remap/1.2/entity/productfolder/'.$category->my_warehouse_id, [
                        "name" => $category->name,
                    ]);
            }
        });

    }

}