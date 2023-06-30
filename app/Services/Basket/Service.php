<?php

namespace App\Services\Basket;

use App\Models\Basket;

class Service
{
    public function create($user, $session_id)
    {
        if ($session_id){
            Basket::firstOrCreate([
                'session_id' => $session_id,
            ]);
        }
        else{
            Basket::firstOrCreate([
                'user_id' => $user->id,
            ]);
        }
    }

    public function update($data, $user)
    {
        $product = (int) $data['product_id'];
        if (isset($data['quantity'])){
            $quantity = (int) $data['quantity'];
        }

        if(\DB::table('basket_product')->where('basket_id', $user->basket->id,)
            ->where('product_id', $product)->first()){

            \DB::table('basket_product')->where('basket_id', $user->basket->id,)
                ->where('product_id', $product)->update([
                    'quantity' => $quantity ?? 1,
                    'updated_at' => date(now()),
            ]);
            return \DB::table('basket_product')->where('basket_id', $user->basket->id,)
                ->where('product_id', $product)->first();
        }
        else{
            return \DB::table('basket_product')->insert([
                'basket_id' => $user->basket->id,
                'product_id' => $product,
                'quantity' => $quantity ?? 1,
                'created_at' => date(now()),
                'updated_at' => date(now()),
            ]);
        }

    }

    public function destroy($data, $user)
    {
        $product = (int) $data['product_id'];

        \DB::table('basket_product')->where('basket_id', $user->basket->id,)
            ->where('product_id', $product)->delete();

        return true;
    }
}