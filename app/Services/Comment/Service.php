<?php

namespace App\Services\Comment;


use App\Models\Category;
use App\Models\Comment;
use App\Models\MyWarehouse;
use App\Models\Product;
use App\Models\Variant;
use App\Utilities\ImageConvertToWebp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Service
{
    public function store($data, $comment)
    {
        DB::transaction(function() use ($data, $comment) {
            $answer = Comment::create([
                'type' => 1,
                'comment' => $data['comment'],
                'user_id' => \Auth::user()->id,
                'product_id' => $comment->product_id,
            ]);
            $comment->update([
                'answer_id' => $answer->id,
            ]);
        });
    }

    public function create($data)
    {
        DB::transaction(function() use ($data) {
            Comment::create([
                'comment' => $data['comment'],
                'advantages' => $data['advantages'],
                'disadvantages' => $data['disadvantages'],
                'rating' => $data['rating'],
                'user_id' => $data['user_id'],
                'product_id' => $data['product_id'],
            ]);
        });
        $this->update_rating($data['product_id']);
    }

    public function update($data, $comment)
    {
        $need_update_rating = false;
        if ($comment->rating !== $data['rating']){
            $need_update_rating = true;
        }
        DB::transaction(function() use ($data, $comment) {
            $comment->update([
                'comment' => $data['comment'],
                'advantages' => $data['advantages'],
                'disadvantages' => $data['disadvantages'],
                'rating' => $data['rating'],
            ]);
        });
        if ($need_update_rating){
            $this->update_rating($comment->product_id);
        }
    }

    private function update_rating($product_id){
        $comments = Comment::where('product_id', $product_id)->where('type', 0)->get();
        if ($comments->count() > 0){
            $mean_rating = round($comments->sum('rating') / $comments->count(), 2);
            $product = Product::where('id', $product_id)->first();
            if ($product){
                $product->update([
                    'rating' => $mean_rating
                ]);
            }
            else{
                foreach (Variant::all() as $variants){
                    $variant = is_string($variants->variants_json) ? json_decode($variants->variants_json, true) : $variants->variants_json;
                    if ($variant['id'] === $product_id){
                        $variant['rating'] = $mean_rating;
                        $variants->update([
                            'variants_json' => json_encode($variant, JSON_UNESCAPED_UNICODE)
                        ]);
                    }
                }
            }
        }
    }
}