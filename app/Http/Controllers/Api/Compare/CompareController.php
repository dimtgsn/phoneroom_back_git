<?php

namespace App\Http\Controllers\Api\Compare;

use App\Http\Controllers\Controller;
use App\Http\Requests\Compare\GetRequest;
use App\Http\Requests\Compare\StoreRequest;
use App\Http\Requests\Compare\UpdateRequest;
use App\Http\Resources\Product\MiniProductCollection;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\Compare\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompareController extends Controller
{
    public function index(User $user, Service $service) {
        if ($user->compare){
            $compare_products = \DB::table('compare_product')->where('compare_id', $user->compare->id)->get();
            $products = [];
            foreach ($compare_products as $product){
                $product_ = Product::find($product->product_id);
                if ($product_){
                    $category = Category::select('id')->where('name', $product_['category'])->first();
                    $product_['category_id'] = $category->id;
                    $products += $product_ ;
                }
                else{
                    $product_variant = \DB::select("
                        select variants_json->>0 as data , product_id
                        from variants
                        where (variants_json->>0)::jsonb  @> '{\"id\": \"$product->product_id\"}'"
                    );
                    if (!$product_variant){
                        $product_variant = \DB::select("
                            select variants_json as data , product_id
                            from variants
                            where (variants_json)::jsonb  @> '{\"id\": \"$product->product_id\"}'"
                        );
                    }
                    $product_variant = is_string($product_variant[0]->data) ? json_decode($product_variant[0]->data, true) : $product_variant[0]->data;
                    $category = Category::select('id')->where('name', $product_variant['category'])->first();
                    $product_variant['category_id'] = $category->id;
                    $product_variant['price'] = (int)$product_variant['price'];
                    $product_variant['old_price'] = (int)$product_variant['old_price'];
                    $products[] = $product_variant ;
                }
            }
            return $products;
        }
        else{
            $service->create($user);
            return [];
        }
    }

    public function category(GetRequest $request, User $user, Service $service){
        if ($user->compare){
            $data = $request->validated();
            $category = Category::select('id')->where('name', $data['category_id'])->first();
            $compare_products = \DB::table('compare_product')->where('compare_id', $user->compare->id)
                ->where('category_id', $category->id)->get();
            $products = [];
            foreach ($compare_products as $product){
                $product_ = Product::find($product->product_id);
                if ($product_){
                    $product_['category_id'] = $category->id;
                    $products += $product_ ;
                }
                else{
                    $product_id = \DB::select("
                        select variants_json->>0 as data , product_id
                        from variants
                        where (variants_json->>0)::jsonb  @> '{\"id\": \"$product->product_id\"}'"
                    );
                    if (!$product_id){
                        $product_id = \DB::select("
                            select variants_json as data , product_id
                            from variants
                            where (variants_json)::jsonb  @> '{\"id\": \"$product->product_id\"}'"
                        );
                    }
                    $product_properties = json_decode(Product::find($product_id[0]->product_id)->property->properties_json, true);
                    $product_variant = \DB::select("
                        select variants_json->>0 as data , product_id
                        from variants
                        where (variants_json->>0)::jsonb  @> '{\"id\": \"$product->product_id\"}'"
                    );
                    if (!$product_variant){
                        $product_variant = \DB::select("
                            select variants_json as data , product_id
                            from variants
                            where (variants_json)::jsonb  @> '{\"id\": \"$product->product_id\"}'"
                        );
                    }
                    $product_variant = is_string($product_variant[0]->data) ? json_decode($product_variant[0]->data, true) : $product_variant[0]->data;
                    $product_variant['category_id'] = $category->id;
                    $product_variant['price'] = (int)$product_variant['price'];
                    $product_variant['old_price'] = (int)$product_variant['old_price'];
                    $product_variant['properties'] = $product_properties;

                    $products[] = $product_variant ;
                }
            }
            return $products;
        }
        else{
            $service->create($user);
            return [];
        }
    }

    public function update(UpdateRequest $request, Service $service, User $user){
        if (!$user->compare){
            $service->create($user);
        }
        $data = $request->validated();
        return $service->update($data, $user);
    }

    public function destroy(UpdateRequest $request, Service $service, User $user){
        $data = $request->validated();
        return $service->destroy($data, $user);
    }

    public function products(Request $request){
        $data = $request->validate([
            'products' => ['required']
        ]);
        if (!count(json_decode($data['products'], true))){
            return [];
        }
        $products = [];
        foreach (json_decode($data['products'], true) as $product) {
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
                $products[] = is_string($variant[0]->data) ? json_decode($variant[0]->data, true) : $variant[0]->data;
            }
        }
        if (count($products)){
            return new MiniProductCollection($products);
        }
        return [];
    }
}
