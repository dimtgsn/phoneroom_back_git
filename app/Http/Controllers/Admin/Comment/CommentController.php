<?php

namespace App\Http\Controllers\Admin\Comment;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Services\Comment\Service;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function create(Comment $comment){

        return view('admin.comment.create', compact('comment'));
    }

    public function store(Comment $comment, Request $request, Service $service){

        $data = $request->validate([
            'comment' => ['required', 'string']
        ]);
        $service->store($data, $comment);
        return view('admin.product.index');
    }
}
