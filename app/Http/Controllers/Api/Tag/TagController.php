<?php

namespace App\Http\Controllers\Api\Tag;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\StoreRequest;
use App\Http\Requests\Tag\UpdateRequest;
use App\Http\Resources\Tag\TagCollection;
use App\Http\Resources\Tag\TagResource;
use App\Models\Product;
use App\Models\Tag;
use App\Services\Tag\Service;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Tag $tag){

        $tag_section = [
            'name' => $tag->name,
        ];
        $product_or_variants = [];
        for($i=0;$i<count($tag->products);$i++){
            if (count($tag->products[$i]->variants)){
                $category = $tag->products[$i]->category;
                foreach ($tag->products[$i]->variants as $j => $variant){
                    $variant_data = is_string($variant->variants_json) ? json_decode($variant->variants_json, true) : $variant->variants_json;
                    $product_or_variants[$j] = [
                        'id' => $variant_data['id'],
                        'product_name' => $variant_data['product_name'],
                        'slug' => $variant_data['slug'],
                        'image' => $variant_data['image'],
                        'price' => $variant_data['price'],
                        'category_name' => $category->name,
                        'category_slug' => $category->slug,
                        'category_id' => $category->id,
                        'quantity' => 1,
                        'old_price' => $variant_data['old_price'],
                        'rating' => (float)$variant_data['rating'],
                        'units_in_stock' => (int)$variant_data['units_in_stock'],
                        'published' => $variant_data['published'],
                        'properties' => is_string($tag->products[$i]->property->properties_json) ? json_decode($tag->products[$i]->property->properties_json, true) : $tag->products[$i]->property->properties_json,
                    ];
                }
            }
            else{
                $product_or_variants[] = [
                   'id' => $tag->products[$i]->id,
                   'product_name' => $tag->products[$i]->name,
                   'slug' => $tag->products[$i]->slug,
                   'image' => $tag->products[$i]->image,
                   'price' => $tag->products[$i]->price,
                   'quantity' => 1,
                   'published' => $tag->products[$i]->published,
                   'category_name' => $tag->products[$i]->category->name,
                   'category_slug' => $tag->products[$i]->category->slug,
                   'category_id' => $category->id,
                   'old_price' => $tag->products[$i]->old_price,
                   'rating' => (float)$tag->products[$i]->rating,
                   'units_in_stock' => (int)$tag->products[$i]->units_in_stock,
                ];
//                $tag->products[$i] = $product_or_variants;
            }
        }
        shuffle($product_or_variants);
        $tag_section += ['products' => $product_or_variants];

//        return $tag_section;
        return  new TagResource($tag_section);
    }

}
