<?php

namespace App\Services\Favorite;

use App\Models\Favorite;

class Service
{
    public function create($user)
    {
        $favorite = Favorite::firstOrCreate([
            'user_id' => $user->id,
        ]);

    }

    public function update($data, $user)
    {
        $product = (int) $data['product_id'];

        if(!(\DB::table('favorite_product')->where('favorite_id', $user->favorite->id,)
            ->where('product_id', $product)->first())){

            return \DB::table('favorite_product')->insert([
                'favorite_id' => $user->favorite->id,
                'product_id' => $product,
                'created_at' => date(now()),
                'updated_at' => date(now()),
            ]);
        }
    }

    public function destroy($data, $user)
    {
        $product = (int) $data['product_id'];

        \DB::table('favorite_product')->where('favorite_id', $user->favorite->id,)
            ->where('product_id', $product)->delete();

        return true;
    }

}