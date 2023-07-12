<?php

namespace App\Http\Controllers\Api\Comment;

use App\Http\Controllers\Controller;
use App\Http\Resources\Comment\CommentCollection;
use App\Models\Comment;
use App\Models\User;
use App\Services\Comment\Service;
use App\Utilities\DateFormatting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index(Request $request){
        $data = $request->validate([
            'product_id' => ['required', 'int'],
            'limit' => ['nullable', 'int'],
        ]);
        if (!$data['limit']){
            $comments = Comment::where('product_id', $data['product_id'])
                ->where('type', 0)
                ->with('user')
                ->orderBy('created_at', 'DESC')
                ->get();

            $answers = Comment::where('product_id', $data['product_id'])
                ->where('type', 1)
                ->with('user')
                ->orderBy('created_at', 'DESC')
                ->get();
        }
        else{
            $comments = Comment::where('product_id', $data['product_id'])
                ->where('type', 0)
                ->with('user')
                ->orderBy('created_at', 'DESC')
                ->limit($data['limit'])
                ->get();
            $answers = [];
        }
        return [
            'comments' => new CommentCollection($comments),
            'answers' => count($answers) ? new CommentCollection($answers) : []
        ];
    }

    public function create(Request $request, Service $service, \App\Services\Order\Service $order_service){

        $data = $request->validate([
            'comment' => ['required', 'string'],
            'advantages' => ['nullable', 'string'],
            'disadvantages' => ['nullable', 'string'],
            'rating' => ['required', 'int'],
            'product_id' => ['required', 'int'],
            'user_id' => ['required', 'int'],
        ]);
        $flag = false;
        $user_orders = User::where('id', $data['user_id'])->with('orders')->first();
        foreach ($user_orders->orders as $order){
            $order_products = $order_service->get_order_products($order, false)['products'];
            foreach ($order_products as $product){
                if ((int)$product['product']['id'] === $data['product_id']){
                    $flag = true;
                }
            }
        }
        if ($flag){
            $service->create($data);
            return true;
        }
        return false;
    }

    public function update(Request $request, Service $service, Comment $comment){

        $data = $request->validate([
            'comment' => ['nullable', 'string'],
            'advantages' => ['nullable', 'string'],
            'disadvantages' => ['nullable', 'string'],
            'rating' => ['nullable', 'int'],
        ]);
        $service->update($data, $comment);
        return true;
    }
}
