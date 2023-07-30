<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;

use App\Http\Requests\Category\StoreRequest;
use App\Http\Resources\Category\CategoryCollection;
use App\Http\Resources\Category\CategoryResource;

use App\Models\Category;
use http\Env\Request;
use Illuminate\Support\Facades\Cache;


class CategoryController extends Controller
{
    public function index(){

        return new CategoryCollection(
            Cache::remember('category', 60*60*24, function (){
                return Category::orderBy('id')->get();
            })
        );

//        return new CategoryCollection(Category::orderBy('id')->get());
    }

    public function show(Category $category){
        $category_section = [
            'name' => $category->name,
            'id' => $category->id,
            'parent_id' => $category->parent_id,
            'slug' => $category->slug,
            'subCategories' => \DB::table('categories')->select('slug', 'name')->where('parent_id', $category->id)->get(),
            'brands' => json_decode($category->brands, true),
            'products' => json_decode($category->products, true),
        ];
        $min_price = 9999999;
        $max_price = -1;
        $subCategories = \DB::table('categories')->where('parent_id', $category->id)->get();

        if ($category->parent_id !== null){
            for($i=0;$i<count($category->products);$i++){
                if (count($category->products[$i]->variants)){
                    $category->products[$i] = json_decode($category->products[$i]->variants, true);
                    for($j=0;$j<count($category->products[$i]);$j++) {
                        $variant_data = is_string($category->products[$i][$j]['variants_json']) ? json_decode($category->products[$i][$j]['variants_json'], true) : $category->products[$i][$j]['variants_json'];
                        if ($variant_data['price'] < $min_price) {
                            $min_price = $variant_data['price'];
                        }
                        if ($variant_data['price'] >= $max_price) {
                            $max_price = $variant_data['price'];
                        }
                    }
                }
                else{
                    if ($category->products[$i]['price'] < $min_price) {
                        $min_price = $category->products[$i]['price'];
                    }
                    if ($category->products[$i]['price'] >= $max_price) {
                        $max_price = $category->products[$i]['price'];
                    }
                }
            }
        }
        else{
            foreach ($subCategories as $subCategory){
                $category = Category::where('id', $subCategory->id)->first();
                for($i=0;$i<count($category->products);$i++){
                    if (count($category->products[$i]->variants)){
                        $category->products[$i] = json_decode($category->products[$i]->variants, true);
                        for($j=0;$j<count($category->products[$i]);$j++) {
                            $variant_data = is_string($category->products[$i][$j]['variants_json']) ? json_decode($category->products[$i][$j]['variants_json'], true) : $category->products[$i][$j]['variants_json'];
                            if ($variant_data['price'] < $min_price) {
                                $min_price = $variant_data['price'];
                            }
                            if ($variant_data['price'] >= $max_price) {
                                $max_price = $variant_data['price'];
                            }
                        }
                    }
                    else{
                        if ($category->products[$i]['price'] < $min_price) {
                            $min_price = $category->products[$i]['price'];
                        }
                        if ($category->products[$i]['price'] >= $max_price) {
                            $max_price = $category->products[$i]['price'];
                        }
                    }
                }
            }
        }

        $category_section += [
            'max_price' => (int)$max_price,
            'min_price' => (int)$min_price,
        ];

        return $category_section;
    }

    public function subCategories($id){
        $subCategories = Category::where('parent_id', (int)$id)
            ->get();
        return $subCategories;
    }

    public function prices(Category $category){
        $category_section = [];
        $min_price = 9999999;
        $max_price = -1;
        for($i=0;$i<count($category->products);$i++){
            if (count($category->products[$i]->variants)){
                $category->products[$i] = json_decode($category->products[$i]->variants, true);
                for($j=0;$j<count($category->products[$i]);$j++) {
                    $variant_data = is_string($category->products[$i][$j]['variants_json']) ? json_decode($category->products[$i][$j]['variants_json'], true) : $category->products[$i][$j]['variants_json'];
                    if ($variant_data['price'] < $min_price) {
                        $min_price = $variant_data['price'];
                    }
                    if ($variant_data['price'] >= $max_price) {
                        $max_price = $variant_data['price'];
                    }
                }
            }
            else{
                if ($category->products[$i]['price'] < $min_price) {
                    $min_price = $category->products[$i]['price'];
                }
                if ($category->products[$i]['price'] >= $max_price) {
                    $max_price = $category->products[$i]['price'];
                }
            }
        }
        $category_section += [
            'max_price' => (int)$max_price,
            'min_price' => (int)$min_price,
        ];

        return $category_section;
    }
}
