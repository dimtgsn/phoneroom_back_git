<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\TagResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Option;
use App\Models\Product;
use App\Models\Property;
use App\Models\PropertyValue;
use App\Models\Tag;
use App\Services\Product\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(){
        $products = Product::orderBy('id')
            ->with('category', 'tags', 'brand', 'property', 'variants_json')
            ->get();
        return new ProductCollection($products);
    }
    public function show($slug){
        $product = Product::where('slug', $slug)->first();
        if (empty($product) === true){
            $variant = DB::select("
                        select variants_json->>0 as data , product_id
                        from variants
                        where (variants_json->>0)::jsonb  @> '{\"slug\": \"$slug\"}'"
            );
            if (empty($variant) != true){
                $productVariant = Product::find($variant[0]->product_id);
                $variant = $variant[0]->data;
                $variant = json_decode($variant, true);

                $variant['category_name'] = $productVariant->category['name'];
                $variant['variants_published'] = [];
                foreach ($productVariant->variants as $variants){
                    $variant['variants_published'][json_decode($variants->variants_json, true)['name']]
                        =json_decode($variants->variants_json, true)['published'];
                }
//                $variant['variants_published']['Graphite 512GB'] = false;
                $variant['category_slug'] = $productVariant->category['slug'];
                $variant['category_id'] = $productVariant->category['id'];
                $variant['brand'] = $productVariant->brand['name'];
                $variant['brand_slug'] = $productVariant->brand['slug'];
                $variant['product_slug'] = $productVariant->slug;
                $variant['brand_image'] = $productVariant->brand['image'];
                $variant['images'] = [array("path" => $variant['image'])];
                foreach ($productVariant->images as $img){
                    if ($img->variant_id == $variant['id']){
                        $variant['images'][] = $img;
                    }
                }
                $variant['properties'] = json_decode($productVariant->property->properties_json);
                $variant['option'] = json_decode($productVariant->option->options_json);
                $variant['tags'] = [];
                foreach ($productVariant->tags as $tag){

                    $variant['tags'][] = array($tag->image, $tag->name);
                }
                return $variant;
            }
            else{
                return null;
            }
        }
        else{
            if (empty($product->tags) != true){
                $tags = [];
                foreach ($product->tags as $tag){
                    $tags[] = array($tag->image, $tag->name);
                }
                $product['tags'] = $tags;
            }
            if (empty($product->images) != true){
                $images[] = [
                    'path' => $product->image,
                ];
                foreach ($product->images as $img){
                    $images[] = ['path' => $img->path];
                }
                $product['images'] = $images;
            }
            return new ProductResource($product);
        }
    }
}
