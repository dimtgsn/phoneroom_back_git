<?php

namespace App\Services\Product;

use App\Models\Category;
use App\Models\CategoryProduct;
use App\Models\CategoryVariantRatingDesc;
use App\Models\Color;
use App\Models\Enter;
use App\Models\Image;
use App\Models\MyWarehouse;
use App\Models\Option;
use App\Models\Product;
use App\Models\Property;
use App\Models\Variant;
use App\Utilities\ImageConvertToBase64;
use App\Utilities\ImageConvertToWebp;
use App\Utilities\TranslationIntoLatin;
use Barryvdh\Debugbar\Twig\Extension\Dump;
use Dflydev\DotAccessData\Data;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Service
{

    public function store($data)
    {
        if (!isset($data['purchase_price'])){
            $data['purchase_price'] = 0;
        }
        if (!isset($data['min_price'])){
            $data['min_price'] = 0;
        }
        if (!isset($data['min_balance'])){
            $data['min_balance'] = 0;
        }
        $data_properties = [];
        if (isset($data['properties'])){
            $data_properties = $data['properties'];
            unset($data['properties']);
        }
        $data_thumb_img = [];
        DB::transaction(function() use ($data, $data_thumb_img, $data_properties) {
            $data['image'] = 'storage/'.\Storage::disk('public')->put('images/products', $data['image']);
            if(str_ends_with($data['image'], "jpg") || str_ends_with($data['image'], "png")
                || str_ends_with($data['image'], "gif") || str_ends_with($data['image'], "jpeg")){
                $data['image'] = ImageConvertToWebp::convert($data['image']);
            }
            for ($i=1; $i<=4; $i++){
                if (isset($data['path_'.$i])){
                    $data['path_'.$i] = 'storage/'.\Storage::disk('public')->put('images/thumbimg', $data['path_'.$i]);
                    if(str_ends_with($data['path_'.$i], "jpg") || str_ends_with($data['path_'.$i], "png")
                        || str_ends_with($data['path_'.$i], "gif") || str_ends_with($data['path_'.$i], "jpeg")){
                        $data['path_'.$i] = ImageConvertToWebp::convert($data['path_'.$i], true);
                    }
                }
            }
            for ($i=1; $i<=4; $i++){
                if (isset($data['path_'.$i])){
                    $data_thumb_img[$i] = $data['path_'.$i];
                    unset($data['path_'.$i]);
                }
            }

            if (isset($data['options'])){
                $options_json = json_encode($data['options'], JSON_UNESCAPED_UNICODE);
                for ($i=0;$i<count($data['options']);$i++){
                    if (isset($data['options'][$i]['values']['colors'])){
                        unset($data['options'][$i]['values']['colors']);
                    }
                }
                $variants = [];
                $num = 0;
                foreach ($data['options'][0]['values'] as $opt1){
                    if (count($data['options']) > 1){
                        $i = 1;
                        $options = [
                            $data['options'][0]['name'] => $opt1,
                        ];
                        while ($i !== count($data['options']) - 1){
                            for ($j=0;$j<count($data['options'][$i]['values']);$j++){
                                $options[$data['options'][$i]['name']][] = $data['options'][$i]['values'][$j];
                            }
                            $i++;
                        }
                        $variants_options = [];
                        if (count($options) > 1){
                            $options_opt1 = $options[$data['options'][0]['name']];
                            unset($options[$data['options'][0]['name']]);
                            foreach ($options as $key => $val){
                                if (is_array($val)){
                                    unset($options[$key]);
                                    for ($x=0;$x<count($val);$x++){
                                        if(count($options) > 0){
                                            foreach ($options as $key2 => $val2){
                                                if (is_array($val2)){
                                                    for ($n=0;$n<count($val2);$n++){
                                                        $variants_options[] = [
                                                            $data['options'][0]['name'] => $options_opt1,
                                                            $key => $val[$x],
                                                            $key2 => $val2[$n],
                                                        ];
                                                    }
                                                }
                                            }
                                        }
                                        else{
                                            $variants_options[] = [
                                                $data['options'][0]['name'] => $options_opt1,
                                                $key => $val[$x],
                                            ];
                                        }
                                    }
                                }
                                break;
                            }
                        }
                        for ($k=0;$k<count($data['options'][$i]['values']);$k++){
                            if (empty($variants_options) !== true){
                                for ($l=0;$l<count($variants_options);$l++){
                                    $variants_options[$l][$data['options'][$i]['name']] = $data['options'][$i]['values'][$k];

                                    $name = '';
                                    $variants_options_names = $variants_options[$l];
                                    if (count($variants_options_names) > 2){
                                        foreach (array_reverse($variants_options_names) as $key => $val) {
                                            if (count($variants_options_names) <= 2){
                                                $name = trim($val.' '.$name);
                                                continue;
                                            }
                                            $name = trim('('.$val.')'.' '.$name);
                                            unset($variants_options_names[$key]);
                                        }
                                    }
                                    $variants[$num] = [
                                        'name' => $name,
                                        'product_name' => $data['name'].' '.$name,
                                        'price' => $data['price'],
                                        'old_price' => $data['old_price'],
                                        'description' => $data['description'],
                                        'units_in_stock' => $data['units_in_stock'],
                                        'min_price' => $data['min_price'],
                                        'min_balance' => $data['min_balance'],
                                        'country' => $data['country'],
                                        'purchase_price' => $data['purchase_price'],
                                        'rating' => $data['rating'],
                                        'options' => $variants_options[$l],
                                        'published' => $data['published'],
                                        'image' => $data['image'],
                                        'created_at' => date(now()),
                                        'updated_at' => date(now()),
                                    ];
                                    $num++;
                                }
                            }
                            else{
                                $options[$data['options'][$i]['name']] = $data['options'][$i]['values'][$k];
                                $name = '';
                                foreach ($options as $key => $val) {
                                    $name = trim($name.' '.$val);
                                }
                                $variants[$num] = [
                                    'name' => $name,
                                    'product_name' => $data['name'].' '.$name,
                                    'price' => $data['price'],
                                    'old_price' => $data['old_price'],
                                    'description' => $data['description'],
                                    'units_in_stock' => $data['units_in_stock'],
                                    'min_price' => $data['min_price'],
                                    'min_balance' => $data['min_balance'],
                                    'country' => $data['country'],
                                    'purchase_price' => $data['purchase_price'],
                                    'rating' => $data['rating'],
                                    'options' => $options,
                                    'published' => $data['published'],
                                    'image' => $data['image'],
                                    'created_at' => date(now()),
                                    'updated_at' => date(now()),
                                ];
                                $num++;
                            }

                        }
                    }
                    else{
                        $variants[$num] = [
                            'name' => trim($opt1),
                            'product_name' => $data['name'].' '.trim($opt1),
                            'price' => $data['price'],
                            'old_price' => $data['old_price'],
                            'description' => $data['description'],
                            'units_in_stock' => $data['units_in_stock'],
                            'min_price' => $data['min_price'],
                            'min_balance' => $data['min_balance'],
                            'country' => $data['country'],
                            'purchase_price' => $data['purchase_price'],
                            'rating' => $data['rating'],
                            'options' => [
                                $data['options'][0]['name'] => $opt1
                            ],
                            'published' => $data['published'],
                            'image' => $data['image'],
                            'created_at' => date(now()),
                            'updated_at' => date(now()),
                        ];
                        $num++;
                    }
                }

                $product = Product::firstOrCreate([
                    'name' => $data['name'],
                    'price' => $variants[0]['price'],
                    'old_price' => $variants[0]['old_price'],
                    'description' => $variants[0]['description'],
                    'units_in_stock' => $variants[0]['units_in_stock'],
                    'vat' => $data['vat'],
                    'min_price' => $data['min_price'],
                    'min_balance' => $data['min_balance'],
                    'country' => $data['country'],
                    'purchase_price' => $data['purchase_price'],
                    'rating' => $variants[0]['rating'],
                    'category_id' => $data['category_id'],
                    'brand_id' => $data['brand_id'],
                    'image' => $data['image'],
                ]);

                $product->update([
                    'sku' => trim(str_replace(" ", "-",strtoupper(TranslationIntoLatin::translate($product->name.' '.$product->id.' '.$product->category_id.' '.$product->brand_id)))),
                ]);

                $category = Category::where('id', $product->category_id)->first();
                foreach ($variants as $i => $variant){
                    $variant['id'] = str($product->id).'00'.str($i+1);
                    $variant['brand'] = $product->brand->name;
                    $variant['category'] = $product->category->name;
                    $variant['slug'] = $variant['slug'] ?? str(TranslationIntoLatin::translate($product->slug.' '.$variant['name']))->slug();
                    $variant['sku'] = trim(str_replace(" ", "-",strtoupper(TranslationIntoLatin::translate($product->name.' '.$variant['id'].' '.$product->category_id.' '.$product->brand_id))));
                    $product_variants = Variant::firstOrCreate([
                        'product_id' => $product->id,
                        'variants_json' => json_encode($variant, JSON_UNESCAPED_UNICODE),
                    ]);
                    $category->variants()->attach($product_variants->id);
                    if (empty($data_thumb_img) !== true){
                        for ($i=1; $i<=count($data_thumb_img); $i++){
                            Image::firstOrCreate([
                                'product_id' => $product->id,
                                'path' => $data_thumb_img[$i],
                                'position' => $i,
                                'variant_id' => $variant['id'],
                            ]);
                        }
                    }

                }

                if (isset($data['tags_id'])){
                    $tags = $data['tags_id'];
                    unset($data['tags_id']);
                    $product->tags()->attach($tags);
                }

                if (empty($data_properties) !== true){
                    $properties = [];
                    for ($i=0;$i<count($data_properties)-1;$i++){
                        if (!is_array($data_properties[$i])){
                            $properties[$data_properties[$i]] = [];
                            for ($j=$i+1;$j<=count($data_properties)-2;$j+=2){
                                if (!is_array($data_properties[$j])){
                                    break;
                                }
                                $properties[$data_properties[$i]] += [$data_properties[$j][0] => $data_properties[$j+1][0]];
                            }
                        }
                    }

                    $property = Property::firstOrCreate([
                        'product_id' => $product->id,
                        'properties_json' => json_encode($properties, JSON_UNESCAPED_UNICODE),
                    ]);
                    $category->properties()->attach($property);
                }

                $option = Option::firstOrCreate([
                    'product_id' => $product->id,
                    'options_json' => $options_json,
                ]);
                $category->options()->attach($option);
            }

            else{

                if (isset($data['tags_id'])){
                    $tags = $data['tags_id'];
                    unset($data['tags_id']);
                    $product = Product::firstOrCreate($data);
                    $product->update([
                        'sku' => trim(str_replace(" ", "-",strtoupper(TranslationIntoLatin::translate($product->name.' '.$product->id.' '.$product->category_id.' '.$product->brand_id)))),
                    ]);
                    $product->tags()->attach($tags);
                }
                else{
                    $product = Product::firstOrCreate($data);
                }
                if (empty($data_thumb_img) !== true){
                    for ($i=1; $i<=count($data_thumb_img); $i++){
                        Image::firstOrCreate([
                            'product_id' => $product->id,
                            'path' => $data_thumb_img[$i],
                            'position' => $i
                        ]);
                    }
                }
                CategoryProduct::firstOrCreate([
                    'category_id' => $product->category_id,
                    'product_id' => $product->id,
                ]);
                $category = Category::where('id', $product->category_id)->first();
                if (empty($data_properties) !== true){
                    $properties = [];
                    for ($i=0;$i<count($data_properties)-1;$i++){
                        if (!is_array($data_properties[$i])){
                            $properties[$data_properties[$i]] = [];
                            for ($j=$i+1;$j<=count($data_properties)-2;$j+=2){
                                if (!is_array($data_properties[$j])){
                                    break;
                                }
                                $properties[$data_properties[$i]] += [$data_properties[$j][0] => $data_properties[$j+1][0]];
                            }
                        }
                    }
                    $property = Property::firstOrCreate([
                        'product_id' => $product->id,
                        'properties_json' => json_encode($properties, JSON_UNESCAPED_UNICODE),
                    ]);
                    $category->properties()->attach($property);
                }
            }
        });
    }

    public function update($data, $product)
    {
        $data_thumb_img = [];

        $data_properties = [];
        if (isset($data['properties'])){
            $data_properties = $data['properties'];
        }
        $myWarehouse = MyWarehouse::select('token')->first();
        DB::transaction(function() use ($data, $data_thumb_img, $product, $data_properties, $myWarehouse) {

            if(empty($data['image']) !== true){
                $data['image'] = 'storage/'.\Storage::disk('public')->put('images/products', $data['image']);
                if ($product->image){
                    Storage::disk('public')->delete(substr($product->image, 8));
                }
                if(str_ends_with($data['image'], "jpg") || str_ends_with($data['image'], "png")
                    || str_ends_with($data['image'], "gif") || str_ends_with($data['image'], "jpeg")){
                    $data['image'] = ImageConvertToWebp::convert($data['image'], true);
                }
            }
            for ($i=1; $i<=4; $i++){
                if (empty($data['path_'.$i]) !== true || (isset($data['del_path_'.$i]) && $data['del_path_'.$i] !== null)){
                    if (count($product->images) !== 0){
                        foreach ($product->images as $img){
                            if ($img->position === $i){
                                Storage::disk('public')->delete(substr($img->path, 8));
                            }
                        }
                    }
                    if (empty($data['path_'.$i]) !== true){
                        $data['path_'.$i] = 'storage/'.\Storage::disk('public')->put('images/thumbimg', $data['path_'.$i]);
                        if(str_ends_with($data['path_'.$i], "jpg") || str_ends_with($data['path_'.$i], "png")
                            || str_ends_with($data['path_'.$i], "gif") || str_ends_with($data['path_'.$i], "jpeg")){
                            $data['path_'.$i] = ImageConvertToWebp::convert($data['path_'.$i], true);
                        }
                    }
                    if (isset($data['del_path_'.$i]) && $data['del_path_'.$i] !== null && isset($data['path_'.$i]) === false){
                        $image = Image::where('product_id', $product->id)
                            ->where('position', $i)->first();
                        if($image){
                            $image->delete();
                            foreach ($product->images as $img){
                                if ($img->position > $i){
                                    Image::where('product_id', $product->id)
                                        ->where('position', $img->position)->update([
                                            'position' => $img->position - 1,
                                        ]);
                                }
                            }
                        }
                    }
                }
            }
            for ($i=1; $i<=4; $i++){
                if (isset($data['path_'.$i])){
                    $data_thumb_img[$i] = $data['path_'.$i];
                    unset($data['path_'.$i]);
                }
            }
            if (isset($data['options'])){
                $variants = [];
                $category = Category::where('id', $product->category_id)->first();
//                if($product->option === null || json_decode($product->option->options_json, true) !== $data['options']){
                $options_json = json_encode($data['options'], JSON_UNESCAPED_UNICODE);
                for ($i=0;$i<count($data['options']);$i++){
                    if (isset($data['options'][$i]['values']['colors'])){
                        unset($data['options'][$i]['values']['colors']);
                    }
                }
                $num = 0;
                foreach ($data['options'][0]['values'] as $opt1){
                    if (count($data['options']) > 1){
                        $i = 1;
                        $options = [
                            $data['options'][0]['name'] => $opt1,
                        ];
                        while ($i !== count($data['options']) - 1){
                            for ($j=0;$j<count($data['options'][$i]['values']);$j++){
                                $options[$data['options'][$i]['name']][] = $data['options'][$i]['values'][$j];
                            }
                            $i++;
                        }
                        $variants_options = [];
                        if (count($options) > 1){
                            $options_opt1 = $options[$data['options'][0]['name']];
                            unset($options[$data['options'][0]['name']]);
                            foreach ($options as $key => $val){
                                if (is_array($val)){
                                    unset($options[$key]);
                                    for ($x=0;$x<count($val);$x++){
                                        if(count($options) > 0){
                                            foreach ($options as $key2 => $val2){
                                                if (is_array($val2)){
                                                    for ($n=0;$n<count($val2);$n++){
                                                        $variants_options[] = [
                                                            $data['options'][0]['name'] => $options_opt1,
                                                            $key => $val[$x],
                                                            $key2 => $val2[$n],
                                                        ];
                                                    }
                                                }
                                            }
                                        }
                                        else{
                                            $variants_options[] = [
                                                $data['options'][0]['name'] => $options_opt1,
                                                $key => $val[$x],
                                            ];
                                        }
                                    }
                                }
                                break;
                            }
                        }
                        for ($k=0;$k<count($data['options'][$i]['values']);$k++){
                            if (empty($variants_options) !== true){
                                for ($l=0;$l<count($variants_options);$l++){
                                    $variants_options[$l][$data['options'][$i]['name']] = $data['options'][$i]['values'][$k];

                                    $name = '';
                                    $variants_options_names = $variants_options[$l];
                                    if (count($variants_options_names) > 2){
                                        foreach (array_reverse($variants_options_names) as $key => $val) {
                                            if (count($variants_options_names) <= 2){
                                                $name = trim($val.' '.$name);
                                                continue;
                                            }
                                            $name = trim('('.$val.')'.' '.$name);
                                            unset($variants_options_names[$key]);
                                        }
                                    }
                                    $variants[$num] = [
                                        'name' => $name,
                                        'product_name' => $data['name'].' '.$name,
                                        'price' => (int)$data['price'] ?? $product->price,
                                        'old_price' => $data['old_price'] ?? $product->old_price,
                                        'description' => $data['description'] ?? $product->description,
                                        'units_in_stock' => $data['units_in_stock'] ?? $product->units_in_stock,
                                        'min_price' => $data['min_price'] ?? $product->min_price,
                                        'min_balance' => $data['min_balance'] ?? $product->min_balance,
                                        'country' => $data['country'] ?? $product->country,
                                        'purchase_price' => $data['purchase_price'] ?? $product->purchase_price,
                                        'rating' => $data['rating'] ?? $product->rating,
                                        'options' => $variants_options[$l],
                                        'published' => $data['published'],
//                                        'image' => $data['image'] ?? $product->image,
                                        'created_at' => date(now()),
                                        'updated_at' => date(now()),
                                    ];
                                    $num++;
                                }
                            }
                            else{
                                $options[$data['options'][$i]['name']] = $data['options'][$i]['values'][$k];
                                $name = '';
                                foreach ($options as $key => $val) {
                                    $name = trim($name.' '.$val);
                                }
                                $variants[$num] = [
                                    'name' => $name,
                                    'product_name' => $data['name'].' '.$name,
                                    'price' => (int)$data['price'] ?? $product->price,
                                    'old_price' => $data['old_price'] ?? $product->old_price,
                                    'description' => $data['description'] ?? $product->description,
                                    'units_in_stock' => $data['units_in_stock'] ?? $product->units_in_stock,
                                    'min_price' => $data['min_price'] ?? $product->min_price,
                                    'min_balance' => $data['min_balance'] ?? $product->min_balance,
                                    'country' => $data['country'] ?? $product->country,
                                    'purchase_price' => $data['purchase_price'] ?? $product->purchase_price,
                                    'rating' => $data['rating'] ?? $product->rating,
                                    'options' => $options,
                                    'published' => $data['published'],
//                                    'image' => $data['image'] ?? $product->image,
                                    'created_at' => date(now()),
                                    'updated_at' => date(now()),
                                ];
                                $num++;
                            }

                        }
                    }
                    else{
                        $variants[$num] = [
                            'name' => trim($opt1),
                            'product_name' => $data['name'].' '.trim($opt1),
                            'price' => (int)$data['price'] ?? $product->price,
                            'old_price' => $data['old_price'] ?? $product->old_price,
                            'description' => $data['description'] ?? $product->description,
                            'units_in_stock' => $data['units_in_stock'] ?? $product->units_in_stock,
                            'min_price' => $data['min_price'] ?? $product->min_price,
                            'min_balance' => $data['min_balance'] ?? $product->min_balance,
                            'country' => $data['country'] ?? $product->country,
                            'purchase_price' => $data['purchase_price'] ?? $product->purchase_price,
                            'rating' => $data['rating'] ?? $product->rating,
                            'options' => [
                                $data['options'][0]['name'] => $opt1
                            ],
                            'published' => $data['published'],
//                            'image' => $data['image'] ?? $product->image,
                            'created_at' => date(now()),
                            'updated_at' => date(now()),
                        ];
                        $num++;
                    }
                }

                if (isset($product->variants)){
                    $variants_edit = Variant::where('product_id', $product->id)->get();

                    for ($l=0;$l<count($variants_edit);$l++){
                        $variants_edit[$l]->delete();
                    }
                }

                $product->update([
                    'name' => $data['name'] ?? $product->name,
                    'image' => $data['image'] ?? $product->image,
                    'price' => (int)$data['price'] ?? $product->price,
                    'units_in_stock' => $data['units_in_stock'] ?? $product->units_in_stock,
                    'vat' => $data['vat'] ?? $product->vat,
                    'min_price' => $data['min_price'] ?? $product->min_price,
                    'min_balance' => $data['min_balance'] ?? $product->min_balance,
                    'country' => $data['country'] ?? $product->country,
                    'purchase_price' => $data['purchase_price'] ?? $product->purchase_price,
                    'description' => $data['description'] ?? $product->description,
                    'rating' => $data['rating'] ?? $product->rating,
                    'category_id' => $data['category_id'] ?? $product->category_id,
                    'brand_id' => $data['brand_id'] ?? $product->brand_id,
//                    'published' => $data['published'] ?? $product->published
                    'published' => false,
                ]);

                $product->update([
                    'sku' => trim(str_replace(" ", "-",strtoupper(TranslationIntoLatin::translate($product->name.' '.$product->id.' '.$product->category_id.' '.$product->brand_id)))),
                ]);

//                if ($product->option && json_decode($product->option->options_json, true) === $data['options']){
//                    foreach ($product->variants as $variant) {
//                        $variants[] = json_decode($variant->variants_json, true);
//                    }
//                }

                foreach ($variants as $i => $variant){
                    $variant['id'] = str($product->id).'00'.str($i+1);
                    $variant['brand'] = $product->brand->name;
                    $variant['category'] = $product->category->name;
                    $variant['slug'] = $variant['slug'] ?? str(TranslationIntoLatin::translate($product->slug.' '.$variant['name']))->slug();
                    $variant['sku'] = trim(str_replace(" ", "-",strtoupper(TranslationIntoLatin::translate($product->name.' '.$variant['id'].' '.$product->category_id.' '.$product->brand_id))));

                    if($product->option === null){
                        $variant['image'] = $product->image;
                        foreach ($product->images as $img){
                            Image::firstOrCreate([
                                'product_id' => $product->id,
                                'path' => $img->path,
                                'position' => $img->position,
                                'variant_id' => $variant['id'],
                            ]);
                            $img->delete();
                        }

//                        $product_variants = Variant::firstOrCreate([
//                            'product_id' => $product->id,
//                            'variants_json' => json_encode($variant, JSON_UNESCAPED_UNICODE),
//                        ]);
                    }
                    else{
                        if(isset($data['image'])){
                            $variant['image'] = $data['image'];
                        }
                        else{
                            foreach ($product->variants as $var){
                                if (json_decode($var->variants_json, true)['name'] === $variant['name']){
                                    $variant['image'] = json_decode($var->variants_json, true)['image'];
                                }
                            }
                            foreach ($product->variants as $var){
                                if (!isset($variant['image'])){
                                    $variant['image'] = $product->image;
                                }
                            }
                        }

                        if (empty($data_thumb_img) !== true){
                            for ($i=1; $i<=count($data_thumb_img); $i++){
                                $image = Image::where('product_id', $product->id)
                                    ->where('position', $i)->where('variant_id', $variant['id'])->first();
                                if($image){
                                    $image->update([
                                        'path' => $data_thumb_img[$i],
                                    ]);
                                }
                                else{
                                    Image::firstOrCreate([
                                        'product_id' => $product->id,
                                        'path' => $data_thumb_img[$i],
                                        'position' => $i,
                                        'variant_id' => $variant['id'],
                                    ]);
                                }
                            }
                        }
                    }

                    $product_variants = Variant::firstOrCreate([
                        'product_id' => $product->id,
                        'variants_json' => json_encode($variant, JSON_UNESCAPED_UNICODE),
                    ]);
                    $variants[$i] = $variant;
//
//                    if (count($product->images) !== 0 && $product->option === null){
//                        foreach ($product->images as $img){
//                            Image::firstOrCreate([
//                                'product_id' => $product->id,
//                                'path' => $img->path,
//                                'position' => $img->position,
//                                'variant_id' => $variant['id'],
//                            ]);
//                            $img->delete();
//                        }
//                    }
//
//                    $category->variants()->sync($product_variants->id);
                }
                if (isset($product->option)){
                    Option::where('product_id', $product->id)->first()->update([
                        'options_json' => $options_json,
                    ]);
                    $option = Option::where('product_id', $product->id)->first();

                    $category->options()->sync($product->option->id);
                }
                else {
                    $option = Option::firstOrCreate([
                        'product_id' => $product->id,
                        'options_json' => $options_json,
                    ]);
                    $category->options()->sync($option->id);
                }
                $variants_edit = Variant::where('product_id', $product->id)->get();

                if ($product->option !== null){
                    $variants = Variant::all();
                    foreach ($variants as $var) {
                        if($var->product_id === $product->id){
                            $category->variants()->attach($var->id);
                        }
                    }
                }
            }
            else{
                $product->update([
                    'name' => $data['name'] ?? $product->name,
                    'image' => $data['image'] ?? $product->image,
                    'price' => (int)$data['price'] ?? $product->price,
                    'old_price' => $data['old_price'] ?? $product->old_price,
                    'units_in_stock' => $data['units_in_stock'] ?? $product->units_in_stock,
                    'vat' => $data['vat'] ?? $product->vat,
                    'min_price' => $data['min_price'] ?? $product->min_price,
                    'min_balance' => $data['min_balance'] ?? $product->min_balance,
                    'country' => $data['country'] ?? $product->country,
                    'purchase_price' => $data['purchase_price'] ?? $product->purchase_price,
                    'description' => $data['description'] ?? $product->description,
                    'rating' => $data['rating'] ?? $product->rating,
                    'category_id' => $data['category_id'] ?? $product->category_id,
                    'brand_id' => $data['brand_id'] ?? $product->brand_id,
                    'published' => $data['published'] ?? $product->published,
                ]);
                $product->update([
                    'sku' => trim(str_replace(" ", "-",strtoupper(TranslationIntoLatin::translate($product->name.' '.$product->id.' '.$product->category_id.' '.$product->brand_id)))),
                ]);

                if (isset($product->option)){
                    Option::where('id', $product->option->id)->delete();
                    if (count($product->images) !== 0){
                        Image::where('product_id', $product->id)->delete();
                        if (empty($data_thumb_img) !== true){
                            foreach ($data_thumb_img as $key => $val) {
                                Image::firstOrCreate([
                                    'product_id' => $product->id,
                                    'path' => $val,
                                    'position' => $key
                                ]);
                            }
                        }
                    }
                    else{
                        if (empty($data_thumb_img) !== true){
                            foreach ($data_thumb_img as $key => $val) {
                                Image::firstOrCreate([
                                    'product_id' => $product->id,
                                    'path' => $val,
                                    'position' => $key
                                ]);
                            }
                        }
                    }
                }
                else{
                    if (count($product->images) !== 0){
                        if (empty($data_thumb_img) !== true){
                            foreach ($data_thumb_img as $key => $val) {
                                $image = Image::where('product_id', $product->id)
                                    ->where('position', $key)->first();
                                if($image){
                                    $image->update([
                                        'product_id' => $product->id,
                                        'path' => $val,
                                        'position' => $key,
                                    ]);
                                }
                                else{
                                    Image::firstOrCreate([
                                        'product_id' => $product->id,
                                        'path' => $val,
                                        'position' => $key
                                    ]);
                                }
                            }
                        }
                    }
                    else{
                        if (empty($data_thumb_img) !== true){
                            foreach ($data_thumb_img as $key => $val) {
                                Image::firstOrCreate([
                                    'product_id' => $product->id,
                                    'path' => $val,
                                    'position' => $key
                                ]);
                            }
                        }
                    }
                }
            }

            $category = Category::where('id', $product->category_id)->first();

            if (isset($data['tags_id'])){
                $tags = $data['tags_id'];
                $product->tags()->sync($tags);
            }
            if (empty($data_properties) !== true){
                $data_properties = $data['properties'];
                $properties = [];
                for ($i=0;$i<count($data_properties)-1;$i++){
                    if (!is_array($data_properties[$i])){
                        $properties[$data_properties[$i]] = [];
                        for ($j=$i+1;$j<=count($data_properties)-2;$j+=2){
                            if (!is_array($data_properties[$j])){
                                break;
                            }
                            $properties[$data_properties[$i]] += [$data_properties[$j][0] => $data_properties[$j+1][0]];
                        }
                    }
                }
                $properties_json = json_encode($properties, JSON_UNESCAPED_UNICODE);
                if ($product->property !== null){
                    $property = $product->property;
                    $property->update([
                        'properties_json' => $properties_json,
                    ]);
                    $category->properties()->sync($property->id);
                }
                else{
                    $property = Property::firstOrCreate([
                        'product_id' => $product->id,
                        'properties_json' => $properties_json,
                    ]);
                    $category->properties()->sync($property->id);
                }
            }
            // TODO поменять всё под сервис MyWarehouse
            //my-warehouse
//            if ($product->exported === true) {
//                $image_base64 = new ImageConvertToBase64();
//                $image_to_jpeg = new ImageConvertToWebp();
//                $image = $image_base64->convert($image_to_jpeg->convert($product->image));
//                $product_price = $product->price * 100;
//                $responsePriceType = Http::withToken($myWarehouse->token)
//                    ->get('https://online.moysklad.ru/api/remap/1.2/context/companysettings/pricetype/default');
//
//                //update product
//                $responseNewProduct = Http::withToken($myWarehouse->token)
//                    ->withHeaders([
//                        "Content-Type" => "application/json"
//                    ])
//                    ->put('https://online.moysklad.ru/api/remap/1.2/entity/product/' . $product->my_warehouse_id, [
//                        "name" => $product->name,
//                        "code" => (string)$product->id,
//                        "article" => $product->sku,
//                        "vat" => (int)$product->vat === -1 ? 0 : (int)$product->vat,
//                        "vatEnabled" => !($product->vat === -1),
//                        "description" => $product->description,
//                        "salePrices" => [
//                            [
//                                'value' => $product_price,
//                                "priceType" => [
//                                    "meta" => json_decode($responsePriceType->body(), JSON_UNESCAPED_UNICODE)['meta'],
//                                ],
//                            ],
//                        ],
//                        "minPrice" => [
//                            "value" => $product->min_price * 100,
//                        ],
//                        "buyPrice" => [
//                            "value" => $product->purchase_price * 100,
//                        ],
//                        'minimumBalance' => (int)$product->min_balance,
//                        "images" => [
//                            [
//                                "filename" => $product->slug,
//                                "content" => $image,
//                            ],
//                        ],
//                    ]);
//                $product->update([
//                    'my_warehouse_id' => json_decode($responseNewProduct->body(), JSON_UNESCAPED_UNICODE)['id']
//                ]);
//
//                //update enter
//                $org = Http::withToken($myWarehouse->token)
//                    ->get('https://online.moysklad.ru/api/remap/1.2/entity/organization');
//
//                $warehouse = Http::withToken($myWarehouse->token)
//                    ->get('https://online.moysklad.ru/api/remap/1.2/entity/store');
//
//
//                if (isset($data['options'])) {
//                    //create characteristics
//                    $responseVariantsAll = Http::withToken($myWarehouse->token)
//                        ->get("https://online.moysklad.ru/api/remap/1.2/entity/variant/metadata");
//                    foreach (json_decode($product->option->options_json, JSON_UNESCAPED_UNICODE) as $option) {
//                        if (isset(json_decode($responseVariantsAll->body(), JSON_UNESCAPED_UNICODE)['characteristics'])) {
//                            foreach (json_decode($responseVariantsAll->body(), JSON_UNESCAPED_UNICODE)['characteristics'] as $row => $variant) {
//                                if ($variant['name'] === $option["name"]) {
//                                    break;
//                                }
//                                if ($row === count(json_decode($responseVariantsAll->body(), JSON_UNESCAPED_UNICODE)['characteristics']) - 1) {
//                                    Http::withToken($myWarehouse->token)
//                                        ->withHeaders([
//                                            "Content-Type" => "application/json"
//                                        ])
//                                        ->post("https://online.moysklad.ru/api/remap/1.2/entity/variant/metadata/characteristics", [
//                                            "name" => $option["name"],
//                                        ]);
//                                }
//                            }
//                        } else {
//                            Http::withToken($myWarehouse->token)
//                                ->withHeaders([
//                                    "Content-Type" => "application/json"
//                                ])
//                                ->post("https://online.moysklad.ru/api/remap/1.2/entity/variant/metadata/characteristics", [
//                                    "name" => $option["name"],
//                                ]);
//                        }
//                    }
//
//                    foreach ($variants as $i => $var) {
//                        $characteristics = [];
//                        foreach ($var['options'] as $key => $val) {
//                            $characteristics[] = [
//                                "name" => $key,
//                                "value" => $val,
//                            ];
//                        }
//                        $responseProductVariant = Http::withToken($myWarehouse->token)
//                            ->withHeaders([
//                                "Content-Type" => "application/json"
//                            ])
//                            ->post('https://online.moysklad.ru/api/remap/1.2/entity/variant', [
//                                "name" => $var['name'],
//                                'characteristics' => $characteristics,
//                                "code" => (string)$var['id'],
//                                "article" => $var['sku'],
//                                "vat" => $product->vat === -1 ? 0 : $product->vat,
//                                "vatEnabled" => !($product->vat === -1),
//                                "description" => $var['description'],
//                                "salePrices" => [
//                                    [
//                                        'value' => $var['price'] * 100,
//                                        "priceType" => [
//                                            "meta" => json_decode($responsePriceType->body(), JSON_UNESCAPED_UNICODE)['meta'],
//                                        ],
//                                    ],
//                                ],
//                                "minPrice" => [
//                                    "value" => $var['min_price'] * 100,
//                                ],
//                                "buyPrice" => [
//                                    "value" => $var['purchase_price'] * 100,
//                                ],
//                                'minimumBalance' => $var['min_balance'],
//                                "product" => [
//                                    "meta" => json_decode($responseNewProduct->body(), JSON_UNESCAPED_UNICODE)['meta'],
//                                ],
//                            ]);
////                        dd(json_decode($responseProductVariant->body()));
//                        $var['my_warehouse_id'] = json_decode($responseProductVariant->body(), JSON_UNESCAPED_UNICODE)['id'];
//                        $variants_edit[$i]->update([
//                            'variants_json' => json_encode($var, JSON_UNESCAPED_UNICODE),
//                        ]);
//
//                        if ((int)$var['units_in_stock'] > 0) {
//                            $responseEnter = Http::withToken($myWarehouse->token)
//                                ->withHeaders([
//                                    "Content-Type" => "application/json"
//                                ])
//                                ->post('https://online.moysklad.ru/api/remap/1.2/entity/enter', [
//                                    "name" => "enter" . $var['id'],
//                                    "organization" => [
//                                        "meta" => json_decode($org->body(), JSON_UNESCAPED_UNICODE)['rows'][0]['meta']
//                                    ],
//                                    "store" => [
//                                        "meta" => json_decode($warehouse->body(), JSON_UNESCAPED_UNICODE)['rows'][0]['meta'] ??
//                                            json_decode($warehouse->body(), JSON_UNESCAPED_UNICODE)['meta']
//                                    ],
//                                    "positions" => [
//                                        [
//                                            "quantity" => (float)$var['units_in_stock'],
//                                            "price" => $var['price'] * 100,
//                                            "assortment" => [
//                                                "meta" => json_decode($responseProductVariant->body(), JSON_UNESCAPED_UNICODE)['meta']
//                                            ]
//                                        ]
//                                    ],
//                                ]);
//                            Enter::firstOrCreate([
//                                'enter_id' => json_decode($responseEnter->body(), JSON_UNESCAPED_UNICODE)['id'],
//                                'product_id' => $product->id,
//                                'variant_id' => $var['id'],
//                            ]);
//                        }
//                    }
//                } else {
//                    if ((int)$product->units_in_stock > 0) {
//                        $responseEnter = Http::withToken($myWarehouse->token)
//                            ->withHeaders([
//                                "Content-Type" => "application/json"
//                            ])
//                            ->put('https://online.moysklad.ru/api/remap/1.2/entity/enter/' . $product->enter->enter_id, [
//                                "name" => "enter" . $product->id,
//                                "organization" => [
//                                    "meta" => json_decode($org->body(), JSON_UNESCAPED_UNICODE)['rows'][0]['meta']
//                                ],
//                                "store" => [
//                                    "meta" => json_decode($warehouse->body(), JSON_UNESCAPED_UNICODE)['rows'][0]['meta'] ??
//                                        json_decode($warehouse->body(), JSON_UNESCAPED_UNICODE)['meta']
//                                ],
//                                "positions" => [
//                                    [
//                                        "quantity" => (int)$product->units_in_stock,
//                                        "price" => $product_price,
//                                        "assortment" => [
//                                            "meta" => json_decode($responseNewProduct->body(), JSON_UNESCAPED_UNICODE)['meta']
//                                        ]
//                                    ]
//                                ],
//                            ]);
//                        $product->enter->update([
//                            'enter_id' => json_decode($responseEnter->body(), JSON_UNESCAPED_UNICODE)['id'],
//                        ]);
//                    }
//                }
//            }
        });
    }

    public function variant_update($data, $product, $variant_slug)
    {

        $data_thumb_img = [];
        $data_properties = [];
        $var_img = null;
        $var_id = 0;
        foreach ($product->variants as $variants){
            if (json_decode($variants->variants_json, true)['slug'] === $variant_slug){
                $var_img = json_decode($variants->variants_json, true)['image'];
                $var_id = json_decode($variants->variants_json, true)['id'];
            }
        }
        if (isset($data['properties'])){
            $data_properties = $data['properties'];
        }
        $myWarehouse = MyWarehouse::select('token')->first();

        DB::transaction(function() use ($data, $data_thumb_img, $variant_slug, $product, $var_img, $var_id, $data_properties, $myWarehouse) {

            if(empty($data['image']) !== true){
                $data['image'] = 'storage/'.\Storage::disk('public')->put('images/products', $data['image']);
                if ($var_img){
                    $flag = false;
                    foreach ($product->variants as $variants){
                        if (json_decode($variants->variants_json, true)['image'] === $var_img &&
                            json_decode($variants->variants_json, true)['id'] !== $var_id){
                            $flag = true;
                            break;
                        }
                    }
                    if (!$flag){
                        Storage::disk('public')->delete(substr($var_img, 8));
                    }
                }
                if(str_ends_with($data['image'], "jpg") || str_ends_with($data['image'], "png")
                    || str_ends_with($data['image'], "gif") || str_ends_with($data['image'], "jpeg")){
                    $data['image'] = ImageConvertToWebp::convert($data['image'], true);
                }
            }
            for ($i=1; $i<=4; $i++){
                if (empty($data['path_'.$i]) !== true || (isset($data['del_path_'.$i]) && $data['del_path_'.$i] !== null)){
                    $flag = false;
                    if (empty($data['path_'.$i]) !== true){
                        $data['path_'.$i] = 'storage/'.\Storage::disk('public')->put('images/thumbimg', $data['path_'.$i]);
                        if(str_ends_with($data['path_'.$i], "jpg") || str_ends_with($data['path_'.$i], "png")
                            || str_ends_with($data['path_'.$i], "gif") || str_ends_with($data['path_'.$i], "jpeg")){
                            $data['path_'.$i] = ImageConvertToWebp::convert($data['path_'.$i], true);
                        }
                    }
                    if ($product->images){
                        foreach ($product->images as $img){
                            if ((int)$img->variant_id === (int)$var_id && $img->position === $i){
                                foreach ($product->images as $image){
                                    if ($img->path === $image->path && (int)$image->variant_id !== (int)$var_id){
                                        $flag = true;
                                        break;
                                    }
                                }
                                if (!$flag){
                                    Storage::disk('public')->delete(substr($img->path, 8));
                                }
                            }
                        }
                    }
                    if (isset($data['del_path_'.$i]) && $data['del_path_'.$i] !== null && isset($data['path_'.$i]) === false){
                        $image = Image::where('product_id', $product->id)
                            ->where('position', $i)->where('variant_id', (int)$var_id)->first();
                        if($image){
                            $image->delete();
                            foreach ($product->images as $img){
                                if ($img->position > $i && (int)$img->variant_id === (int)$var_id){
                                    Image::where('product_id', $product->id)
                                        ->where('position', $img->position)->where('variant_id', (int)$var_id)->update([
                                            'position' => $img->position - 1,
                                        ]);
                                }
                            }
                        }
                    }

                }
            }

            for ($i=1; $i<=4; $i++){
                if (isset($data['path_'.$i])){
                    $data_thumb_img[$i] = $data['path_'.$i];
                    unset($data['path_'.$i]);
                }
//                else{
//                    $data_thumb_img[$i] = '';
//                }
            }

            $variants = [];

            if (isset($product->variants)){
                foreach ($product->variants as $i => $product_variant){
                    if (json_decode($product_variant->variants_json, true)['slug'] === $variant_slug){
                        $variant = [
                            'name' => json_decode($product_variant->variants_json, true)['name'],
                            'product_name' => $data['name'] ?? json_decode($product_variant->variants_json, true)['product_name'],
                            'price' => $data['price'] ?? json_decode($product_variant->variants_json, true)['price'],
                            'old_price' => $data['old_price'] ?? json_decode($product_variant->variants_json, true)['old_price'],
                            'description' => $data['description'] ?? json_decode($product_variant->variants_json, true)['description'],
                            'units_in_stock' => $data['units_in_stock'] ?? json_decode($product_variant->variants_json, true)['units_in_stock'],
                            'min_price' => $data['min_price'] ?? json_decode($product_variant->variants_json, true)['min_price'],
                            'min_balance' => $data['min_balance'] ?? json_decode($product_variant->variants_json, true)['min_balance'],
                            'country' => $data['country'] ?? json_decode($product_variant->variants_json, true)['country'],
                            'purchase_price' => $data['purchase_price'] ?? json_decode($product_variant->variants_json, true)['purchase_price'],
                            'rating' => $data['rating'] ?? json_decode($product_variant->variants_json, true)['rating'],
                            'options' => json_decode($product_variant->variants_json, true)['options'],
                            'published' => $data['published'],
                            'image' => $data['image'] ?? json_decode($product_variant->variants_json, true)['image'],
                            'id' => json_decode($product_variant->variants_json, true)['id'],
                            'created_at' => json_decode($product_variant->variants_json, true)['created_at'] ?? date(now()),
                            'updated_at' => date(now()),
                        ];

                        $variant['brand'] = json_decode($product_variant->variants_json, true)['brand'];
                        $variant['category'] = json_decode($product_variant->variants_json, true)['category'];
                        $variant['slug'] = $variant['slug'] ?? str(TranslationIntoLatin::translate($product->slug.' '.$variant['name']))->slug();
                        $variant['sku'] = trim(str_replace(" ", "-",strtoupper(TranslationIntoLatin::translate($product->name.' '.$variant['id'].' '.$product->category_id.' '.$product->brand_id))));
//                        if($product->exported){
//                            $variant['my_warehouse_id'] = json_decode($product_variant->variants_json, true)['my_warehouse_id'];
//                        }
                        Variant::find($product_variant->id)->update([
                            'variants_json' => json_encode($variant, JSON_UNESCAPED_UNICODE),
                        ]);

                        if (empty($data_thumb_img) !== true){
                            if (count($product->images) !== 0){
                                foreach ($data_thumb_img as $key => $val) {
                                        $img = Image::where('product_id', $product->id)
                                            ->where('position', $key)
                                            ->where('variant_id', $variant['id'])
                                            ->first();
                                        if ($img){
                                            $img->update([
                                                    'product_id' => $product->id,
                                                    'path' => $val,
                                                    'position' => $key,
                                                    'variant_id' => $variant['id'],
                                                ]);
                                        }
                                        else{
                                            Image::firstOrCreate([
                                                'product_id' => $product->id,
                                                'path' => $val,
                                                'position' => $key,
                                                'variant_id' => $variant['id'],
                                            ]);
                                        }

                                }
                            }
                            else{
                                foreach ($data_thumb_img as $key => $val) {
                                    Image::firstOrCreate([
                                        'product_id' => $product->id,
                                        'path' => $val,
                                        'position' => $key,
                                        'variant_id' => $variant['id'],
                                    ]);

                                }
                            }
                        }

                        //my-warehouse
//                        if($product->exported){
//                            $responsePriceType = Http::withToken($myWarehouse->token)
//                                ->get('https://online.moysklad.ru/api/remap/1.2/context/companysettings/pricetype/default');
//                            $responseProductVariant = Http::withToken($myWarehouse->token)
//                                ->withHeaders([
//                                    "Content-Type" => "application/json"
//                                ])
//                                ->put('https://online.moysklad.ru/api/remap/1.2/entity/variant/'.$variant['my_warehouse_id'], [
//                                    "name" => $variant['name'],
//                                    "article" => $variant['sku'],
//                                    "description" => $variant['description'],
//                                    "salePrices" => [
//                                        [
//                                            'value' => $variant['price'] * 100,
//                                            "priceType" => [
//                                                "meta" => json_decode($responsePriceType->body(), JSON_UNESCAPED_UNICODE)['meta'],
//                                            ],
//                                        ],
//                                    ],
//                                    "minPrice" => [
//                                        "value" => $variant['min_price'] * 100,
//                                    ],
//                                    "buyPrice" => [
//                                        "value" => $variant['purchase_price'] * 100,
//                                    ],
//                                    'minimumBalance' => $variant['min_balance'],
//                                ]);
////                        dd(json_decode($responseProductVariant->body(), JSON_UNESCAPED_UNICODE));
//                            $variant['my_warehouse_id'] = json_decode($responseProductVariant->body(), JSON_UNESCAPED_UNICODE)['id'];
//                            Variant::find($product_variant->id)->update([
//                                'variants_json' => json_encode($variant, JSON_UNESCAPED_UNICODE),
//                            ]);
//
//                            //update enter
//                            $org = Http::withToken($myWarehouse->token)
//                                ->get('https://online.moysklad.ru/api/remap/1.2/entity/organization');
//
//                            $warehouse = Http::withToken($myWarehouse->token)
//                                ->get('https://online.moysklad.ru/api/remap/1.2/entity/store');
//
//                            $enter = Enter::where('product_id', $product->id)->where('variant_id', $variant['id'])->first();
//                            $responseEnter = Http::withToken($myWarehouse->token)
//                                ->withHeaders([
//                                    "Content-Type" => "application/json"
//                                ])
//                                ->put('https://online.moysklad.ru/api/remap/1.2/entity/enter/'.$enter->enter_id, [
//                                    "organization" => [
//                                        "meta" => json_decode($org->body(), JSON_UNESCAPED_UNICODE)['rows'][0]['meta']
//                                    ],
//                                    "store" => [
//                                        "meta" => json_decode($warehouse->body(), JSON_UNESCAPED_UNICODE)['rows'][0]['meta'] ??
//                                            json_decode($warehouse->body(), JSON_UNESCAPED_UNICODE)['meta']
//                                    ],
//                                    "positions" => [
//                                        [
//                                            "quantity" => (float)$variant['units_in_stock'],
//                                            "price" => $variant['price'] * 100,
//                                            "assortment" => [
//                                                "meta" => json_decode($responseProductVariant->body(), JSON_UNESCAPED_UNICODE)['meta']
//                                            ]
//                                        ]
//                                    ],
//                                ]);
//                        }
                    }
                }
            }
        });
    }

}