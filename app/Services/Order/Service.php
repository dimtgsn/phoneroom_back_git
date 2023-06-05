<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\Product;
use App\Models\Variant;

class Service
{
    public function create($data)
    {
        return \DB::transaction(function() use ($data) {

            $order = Order::firstOrCreate([
                'user_id' => $data['user_id'],
                'status_id' => 5,
                'total' => (int)$data['total'],
                'ship_address' => json_decode($data['ship_address'], true)['address'],
                'zip' => json_decode($data['ship_address'], true)['zip'],
            ]);
            foreach (json_decode($data['details'], true) as $product){
                $pr = Product::where('id', $product['id'])->first();
                if ($pr){
                    $price = $pr->price;
                } else{
                    foreach (Variant::all() as $variants){
                        if (json_decode($variants->variants_json, true)['id'] === $product['id']){
                            $price = (int)json_decode($variants->variants_json, true)['price'];
                        }
                    }
                }
                \DB::table('order_products')->insert([
                    'quantity' => $product['quantity'],
                    'product_id' => $product['id'],
                    'price' => $price,
                    'order_id' => $order->id,
                ]);
            }

        });
    }


//    public function destroy($data, $user)
//    {
//        $product = (int) $data['product_id'];
//
//        \DB::table('basket_product')->where('basket_id', $user->basket->id,)
//            ->where('product_id', $product)->delete();
//
//        return true;
//    }
}