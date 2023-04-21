<?php

namespace App\Http\Controllers\Admin\Tag;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\StoreRequest;
use App\Http\Requests\Tag\UpdateRequest;
use App\Models\Product;
use App\Models\Tag;
use App\Services\Tag\Service;
use App\Utilities\ImageConvertToWebp;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request){


        $tags = Tag::orderBy('id')->paginate(15);

        return view('admin.tag.index', compact('tags'));
    }

    public function create(){

        $products = Product::all();
        return view('admin.tag.create', compact('products'));
    }

    public function store(StoreRequest $request, Service $service){

        $data = $request->validated();

        $service->store($data);
        return redirect()->route('admin.tags.index');
    }

    public function show(Tag $tag)
    {
        return view('admin.tag.show', compact('tag'));
    }

    public function edit(Tag $tag)
    {
        $products = Product::all();
        return view('admin.tag.edit', compact('tag', 'products'));
    }

    public function update(Tag $tag, UpdateRequest $request, Service $service){

        $data = $request->validated();

        $service->update($data, $tag);
        return redirect()->route('admin.tags.index');
    }

    public function destroy(Tag $tag){
        $tag->delete();
        if($tag->image){
            \Storage::disk('public')->delete(substr($tag->image, 8));
        }
        return redirect()->route('admin.tags.index');
    }
}
