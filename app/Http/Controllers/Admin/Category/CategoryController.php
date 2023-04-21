<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreRequest;
use App\Http\Requests\Category\UpdateRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MyWarehouse;
use App\Services\Category\Service;
use App\Utilities\ImageConvertToWebp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CategoryController extends Controller
{
    public function index(){

        $categories = Category::orderBy('id')->with('brands')->paginate(42);

        return view('admin.category.index', compact('categories'));
    }

    public function create(){
        $brands = Brand::all();
        $categories = Category::all();
        return view('admin.category.create', compact('categories', 'brands'));
    }

    public function store(StoreRequest $request, Service $service){

        $data = $request->validated();
        $service->store($data);
        return redirect()->route('admin.categories.index');
    }

    public function show(Category $category)
    {
        $parentCategory = Category::query()
                ->select('*')
                ->where('id', $category->parent_id)
                ->first();
        return view('admin.category.show', compact('category', 'parentCategory'));
    }

    public function edit(Category $category)
    {
        $brands = Brand::all();
        $categories = Category::all();
        $parentCategory = Category::query()
            ->select('*')
            ->where('id', $category->parent_id)
            ->first();
        return view('admin.category.edit', compact('category', 'categories', 'parentCategory', 'brands'));
    }

    public function update(Category $category, UpdateRequest $request, Service $service){

        $data = $request->validated();
        $service->update($data, $category);
        return redirect()->route('admin.categories.index');
    }


    public function destroy(Category $category){
        if ($category->my_warehouse_id){
            $myWarehouse = MyWarehouse::select('token')->first();
            foreach ($category->products as $product) {
                if ($product->my_warehouse_id){
                    if ($product->enter){
                        Http::withToken($myWarehouse->token)
                            ->delete('https://online.moysklad.ru/api/remap/1.2/entity/enter/'.$product->enter->enter_id);
                        $product->enter->delete();
                    }
                    Http::withToken($myWarehouse->token)
                        ->delete('https://online.moysklad.ru/api/remap/1.2/entity/product/'.$product->my_warehouse_id);
                }
            }
            Http::withToken($myWarehouse->token)
                ->delete('https://online.moysklad.ru/api/remap/1.2/entity/productfolder/'.$category->my_warehouse_id);
        }

        $category->delete();
        if($category->image){
            \Storage::disk('public')->delete(substr($category->image, 8));
        }
        return redirect()->route('admin.categories.index');
    }
}
