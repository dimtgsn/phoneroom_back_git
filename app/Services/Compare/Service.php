<?php

namespace App\Services\Compare;

use App\Models\Compare;

class Service
{
    public function create($user)
    {;
        $compare = Compare::firstOrCreate([
            'user_id' => $user->id,
        ]);
    }

    public function             update($data, $user)
    {
        $product = (int) $data['product_id'];
        $category = (int) $data['category_id'];

        if(!(\DB::table('compare_product')->where('compare_id', $user->compare->id,)
            ->where('product_id', $product)
            ->where('category_id', $category)->first())){

            return \DB::table('compare_product')->insert([
                'compare_id' => $user->compare->id,
                'product_id' => $product,
                'category_id' => $category,
                'created_at' => date(now()),
                'updated_at' => date(now()),
            ]);
        }
    }

    public function destroy($data, $user)
    {
        $product = (int) $data['product_id'];
        $category = (int) $data['category_id'];

        \DB::table('compare_product')->where('compare_id', $user->compare->id,)
            ->where('product_id', $product)
            ->where('category_id', $category)->delete();

        return true;
    }

}