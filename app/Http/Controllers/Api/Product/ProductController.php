<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\Product\MiniProductCollection;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\TagResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Comment;
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
        $product = Product::where('slug', $slug)
            ->with('category', 'tags', 'brand', 'property', 'variants')
            ->first();
        if (empty($product) === true){
            $variant = DB::select("
                select variants_json->>0 as data , product_id
                from variants
                where (variants_json->>0)::jsonb  @> '{\"slug\": \"$slug\"}'"
            );
            if (!$variant){
                $variant = \DB::select("
                    select variants_json as data , product_id
                    from variants
                    where (variants_json)::jsonb  @> '{\"slug\": \"$slug\"}'"
                );
            }
            if (empty($variant) != true){
                $productVariant = Product::find($variant[0]->product_id);
                $variant = is_string($variant[0]->data) ? json_decode($variant[0]->data, true) : $variant[0]->data;
                $comments = Comment::where('product_id', $variant['id'])->where('type', 0)->get();
                $variant['comments_count'] = $comments->count() ?? 0;
                $variant['comments_count_5'] = $comments->where('rating', 5)->count() ?? 0;
                $variant['comments_count_4'] = $comments->where('rating', 4)->count() ?? 0;
                $variant['comments_count_3'] = $comments->where('rating', 3)->count() ?? 0;
                $variant['comments_count_2'] = $comments->where('rating', 2)->count() ?? 0;
                $variant['comments_count_1'] = $comments->where('rating', 1)->count() ?? 0;
                $variant['category_name'] = $productVariant->category['name'];
                $variant['variants_published'] = [];
                foreach ($productVariant->variants as $variants){
                    $variant_data = is_string($variants->variants_json) ? json_decode($variants->variants_json, true) : $variants->variants_json;
                    $variant['variants_published'][$variant_data['name']] = $variant_data['published'];
                }
//                $variant['variants_published']['Graphite 512GB'] = false;
                $variant['category_slug'] = $productVariant->category['slug'];
                $variant['category_id'] = $productVariant->category['id'];
                $variant['brand'] = $productVariant->brand['name'];
                $variant['brand_slug'] = $productVariant->brand['slug'];
                $variant['product_slug'] = $productVariant->slug;
                $variant['brand_image'] = $productVariant->brand['image'];
                $variant['rating'] = (float)$variant['rating'];
                $variant['images'] = [array("path" => $variant['image'])];
                foreach ($productVariant->images as $img){
                    if ($img->variant_id == $variant['id']){
                        $variant['images'][] = $img;
                    }
                }
                $variant['properties'] = is_string($productVariant->property->properties_json) ? json_decode($productVariant->property->properties_json, true) : $productVariant->property->properties_json;
                $variant['option'] = is_string($productVariant->option->options_json) ? json_decode($productVariant->option->options_json, true) : $productVariant->option->options_json;
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
            $comments = Comment::where('product_id', $product['id'])->where('type', 0)->get();
            $product['comments_count'] = $comments->count() ?? 0;
            $product['comments_count_5'] = $comments->where('rating', 5)->count() ?? 0;
            $product['comments_count_4'] = $comments->where('rating', 4)->count() ?? 0;
            $product['comments_count_3'] = $comments->where('rating', 3)->count() ?? 0;
            $product['comments_count_2'] = $comments->where('rating', 2)->count() ?? 0;
            $product['comments_count_1'] = $comments->where('rating', 1)->count() ?? 0;
            return new ProductResource($product);
        }
    }

    public function products(Request $request){
        $data = $request->validate([
            'products' => ['required']
        ]);
        $products = [];
        if (!count(json_decode($data['products'], true))){
            return [];
        }
        foreach (json_decode($data['products'], true) as $product) {
            $product = is_array($product) ? $product[0] : $product;
            $_product = Product::where('id', (int)$product)->first();
            if ($_product){
                $products[] = $_product->toArray();
            }
            else{
                $variant = DB::select("
                    select variants_json->>0 as data
                    from variants
                    where (variants_json->>0)::jsonb  @> '{\"id\": \"$product\"}'"
                );
                if (!$variant){
                    $variant = \DB::select("
                        select variants_json as data
                        from variants
                        where (variants_json)::jsonb  @> '{\"id\": \"$product\"}'"
                    );
                }
                $product_data = is_string($variant[0]->data) ? json_decode($variant[0]->data, true) : $variant[0]->data;
                $product_data['price'] = (int)$product_data['price'];
                $product_data['old_price'] = (int)$product_data['old_price'];
                $product_data['category_id'] = (int)Category::where('name', $product_data['category'])->first()['id'];
                $products[] = $product_data;
            }
        }
        if (count($products)){
            return new MiniProductCollection($products);
        }
        return [];
    }
}
