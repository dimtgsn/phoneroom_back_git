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
                    $product_or_variants[$j] = [
                        'id' => json_decode($variant->variants_json, true)['id'],
                        'product_name' => json_decode($variant->variants_json, true)['product_name'],
                        'slug' => json_decode($variant->variants_json, true)['slug'],
                        'image' => json_decode($variant->variants_json, true)['image'],
                        'price' => json_decode($variant->variants_json, true)['price'],
                        'category_name' => $category->name,
                        'category_slug' => $category->slug,
                        'category_id' => $category->id,
                        'quantity' => 1,
                        'old_price' => json_decode($variant->variants_json, true)['old_price'],
                        'rating' => (float)json_decode($variant->variants_json, true)['rating'],
                        'units_in_stock' => (int)json_decode($variant->variants_json, true)['units_in_stock'],
                        'published' => json_decode($variant->variants_json, true)['published'],
                        'properties' => json_decode($tag->products[$i]->property->properties_json, true),
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
