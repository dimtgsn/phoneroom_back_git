<?php

namespace App\Http\Controllers\Api\Favorite;

use App\Http\Controllers\Controller;
use App\Http\Requests\Favorite\StoreRequest;
use App\Http\Requests\Favorite\UpdateRequest;
use App\Models\Product;
use App\Models\User;
use App\Services\Favorite\Service;

class FavoriteController extends Controller
{
    public function index(User $user, Service $service) {

        if ($user->favorite){
            $favorite_products = \DB::table('favorite_product')->where('favorite_id', $user->favorite->id,)->get();
            $products = [];
            foreach ($favorite_products as $product){
                $product_ = Product::find($product->product_id);
                if ($product_){
                    $products += $product_ ;
                }
                else{
                    $product_variant = json_decode(\DB::select("
                        select variants_json->>0 as data , product_id
                        from variants
                        where (variants_json->>0)::jsonb  @> '{\"id\": \"$product->product_id\"}'"
                    )[0]->data, true);
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

    public function update(UpdateRequest $request, Service $service, User $user){
        if (!$user->favorite){
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
