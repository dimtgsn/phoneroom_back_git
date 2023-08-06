<?php

namespace App\Services\Retail1C;

use App\Models\Category;
use Illuminate\Support\Facades\Log;

class Service
{
    public function store($import_xml, $offers_xml)
    {
        $products = [];
        $categories = [];
        foreach($import_xml->Классификатор->Группы->Группа as $group){
            if ($group->Группы){
                $category_parent = [
                    'name' => (string)$group->Наименование,
                    'parent_id' => null
                ];
                $categories[] = $category_parent;
                foreach($group->Группы->Группа as $sub_group) {
                    $categories[] = [
                        'parent_id' => $category_parent->id ?? (string)$category_parent['name'],
                        'name' => (string)$sub_group->Наименование,
                        'my_warehouse_id' => (string)$sub_group->Ид,
                    ];
                }
            }
            else{
                $categories[] = [
                    'name' => (string)$group->Наименование,
                    'parent_id' => null,
                    'my_warehouse_id' => (string)$group->Ид,
                ];
            }
        }
        foreach ($import_xml->Каталог->Товары->Товар as $product) {
            $product_data = [
                'name' => (string)$product->Наименование,
                'category_id' => Category::where('my_warehouse_id', $product->Категория)->first()['id']
                    ?? (string)$product->Категория,
                'description' => (string)$product->Описание ?? null,
                'vat' => (int)$product->СтавкиНалогов->СтавкаНалога->Ставка ?? null,
                'my_warehouse_id' => (string)$product->Ид ?? null,
            ];
            foreach ($offers_xml->ПакетПредложений->Предложения->Предложение as $offer) {
                if ((string)$offer->Ид === (string)$product->Ид)
                $product_data += [
                    'price' => (int)$offer->Цены->Цена->ЦенаЗаЕдиницу,
                    'units_in_stock' => (int)$offer->Количество
                ];
            }
            $products[] = $product_data;
        }


        Log::info($categories);
        Log::info($products);

//        DB::transaction(function() use ($data) {
//            if (isset($data['image'])){
//                $data['image'] = 'storage/'.\Storage::disk('public')->put('images/categories', $data['image']);
//                if(str_ends_with($data['image'], "jpg") || str_ends_with($data['image'], "png")
//                    || str_ends_with($data['image'], "gif") || str_ends_with($data['image'], "jpeg")){
//                    $data['image'] = ImageConvertToWebp::convert($data['image'], true);
//                }
//            }
//            if (isset($data['brands_id'])){
//                $brands = $data['brands_id'];
//                unset($data['brands_id']);
//                $categoty = Category::firstOrCreate($data);
//                $categoty->brands()->attach($brands);
//            }
//            else{
//                Category::firstOrCreate($data);
//            }
//        });
    }
}