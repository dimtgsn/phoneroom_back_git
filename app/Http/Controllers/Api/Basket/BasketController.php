<?php

namespace App\Http\Controllers\Api\Basket;

use App\Http\Controllers\Controller;
use App\Http\Requests\Basket\StoreRequest;
use App\Http\Requests\Basket\UpdateRequest;
use App\Http\Resources\Product\MiniProductCollection;
use App\Http\Resources\Product\ProductCollection;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\Basket\Service;

class BasketController extends Controller
{
    public function index(User $user, Service $service ){
        if ($user->basket){
            $basket_products = \DB::table('basket_product')->where('basket_id', $user->basket->id,)->get();
            $products = [];
            if (count($basket_products)){
                foreach ($basket_products as $product){
                    $product_ = Product::where('id', (int)$product->product_id)->first();
                    if ($product_){
                        $product_ = $product_->toArray();
                        if ($product->quantity > 1){
                            $product_['quantity'] = $product->quantity;
                        }
                        if ($product->quantity === 1){
                            $product_['quantity'] = $product->quantity;
                        }
                        $products[] = $product_ ;
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
                        $product_variant['price'] = (int)$product_variant['price'];
                        $product_variant['old_price'] = (int)$product_variant['old_price'];
                        $product_variant['category_id'] = (int)Category::where('name', $product_variant['category'])->first()['id'];

                        if ($product->quantity > 1){
                            $product_variant['quantity'] = $product->quantity;
                        }
                        if ($product->quantity === 1){
                            $product_variant['quantity'] = $product->quantity;
                        }
                        $products[] = $product_variant ;
                    }
                }
            }
            return new MiniProductCollection($products);
        }
        else{
            if ($user){
                $service->create($user, null);
                return '';
            }
            $service->create(null, session()->getId());
            return '';
        }
    }

    public function count(User $user){

        if ($user->basket){
            $basket_products_count = count(\DB::table('basket_product')->where('basket_id', $user->basket->id,)->get());
            return $basket_products_count;
        }
    }

    public function update(UpdateRequest $request, Service $service, User $user){
        if (!$user->basket){
            $service->create($user);
        }
        $data = $request->validated();

        return $service->update($data, $user);
    }

    public function destroy(UpdateRequest $request, Service $service, User $user){
        $data = $request->validated();
        return $service->destroy($data, $user);
    }

}
