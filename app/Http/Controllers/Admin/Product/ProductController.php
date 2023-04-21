<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Enter;
use App\Models\Image;
use App\Models\MyWarehouse;
use App\Models\Option;
use App\Models\Product;
use App\Models\Property;
use App\Models\PropertyValue;
use App\Models\Tag;
use App\Models\Variant;
use App\Services\Product\Service;
use App\Utilities\ImageConvertToWebp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(){
        $products = Product::orderBy('id')->with('category', 'tags', 'brand', 'property', 'variants')->paginate(15);
        return view('admin.product.index', compact('products'));
    }

    public function create(){
        $tags = Tag::all();
        $brands = Brand::all();
        $categories = Category::all();
        return view('admin.product.create', compact('categories', 'brands', 'tags'));
    }

    public function store(StoreRequest $request, Service $service){
        $data = $request->validated();
        $data['published'] = isset($data['published']) ? (bool)$data['published']:false;
        $service->store($data);
        return redirect()->route('admin.products.index');
    }

    public function show(Product $product)
    {
        $variant = [];
//        $product1 = Product::with('variants')->get();
//        dd($product->variants);
        return view('admin.product.show', compact('product', 'variant'));
    }

    public function variant_show(Product $product, $variant_slug)
    {
        $variant = [];
        if (isset($product->variants)){
            foreach ($product->variants as $variants){

                if (json_decode($variants->variants_json, true)['slug'] === $variant_slug){
                    $variant = json_decode($variants->variants_json, true);
                }
            }
        }
        return view('admin.product.show', compact('product', 'variant'));
    }

    public function edit(Product $product)
    {
        $brands = Brand::all();
        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.product.edit', compact('product', 'categories', 'brands', 'tags'));
    }

    public function variant_edit(Product $product, $variant_slug)
    {
        $variant = [];
        if (isset($product->variants)){
            foreach ($product->variants as $variants){

                if (json_decode($variants->variants_json, true)['slug'] === $variant_slug){
                    $variant = json_decode($variants->variants_json, true);
                }
            }
        }
        return view('admin.product.variant_edit', compact('product', 'variant'));
    }

    public function update(Product $product, UpdateRequest $request, Service $service){
        $data = $request->validated();
        $data['published'] = isset($data['published']) ? (bool)$data['published']:false;

        $service->update($data, $product);
        return redirect()->route('admin.products.index');
    }

    public function variant_update(Product $product, $variant_slug, UpdateRequest $request, Service $service){
        $data = $request->validated();
        $data['published'] = isset($data['published']) ? (bool)$data['published']:false;

        $service->variant_update($data, $product, $variant_slug);
        return redirect()->route('admin.products.index');
    }

    public function destroy(Product $product){

        if ($product->my_warehouse_id){
            $myWarehouse = MyWarehouse::select('token')->first();
            if ($product->enter){
                if ($product->option){
                    $enters = Enter::where('product_id', $product->id)->get();
                    foreach ($enters as $enter){
                        Http::withToken($myWarehouse->token)
                            ->delete('https://online.moysklad.ru/api/remap/1.2/entity/enter/'.$enter->enter_id);
                    }
                }
                else{
                    Http::withToken($myWarehouse->token)
                        ->delete('https://online.moysklad.ru/api/remap/1.2/entity/enter/'.$product->enter->enter_id);
                }
                $product->enter->delete();
            }

            Http::withToken($myWarehouse->token)
                ->delete('https://online.moysklad.ru/api/remap/1.2/entity/product/'.$product->my_warehouse_id);
        }

        $product->delete();
        if($product->image){
            \Storage::disk('public')->delete(substr($product->image, 8));
        }
        if($product->images){
            foreach ($product->images as $image) {
                \Storage::disk('public')->delete(substr($image->path, 8));
            }
        }
        return redirect()->route('admin.products.index');
    }

    public function variant_destroy(Product $product, $variant_slug){

        foreach ($product->variants as $variants){
            if (json_decode($variants->variants_json, true)['slug'] === $variant_slug){

                if ($product->my_warehouse_id){
                    $myWarehouse = MyWarehouse::select('token')->first();
                    $enter = Enter::where('product_id', $product->id)->where('variant_id', json_decode($variants->variants_json, true)['id'])->first();
                    if ($enter){
                        Http::withToken($myWarehouse->token)
                            ->delete('https://online.moysklad.ru/api/remap/1.2/entity/enter/'.$enter->enter_id);
                        $enter->delete();
                    }

                    Http::withToken($myWarehouse->token)
                        ->delete('https://online.moysklad.ru/api/remap/1.2/entity/variant/'.json_decode($variants->variants_json, true)['my_warehouse_id']);
                }

                Variant::where('variants_json', json_encode($variants->variants_json, true))->delete();
                if(json_decode($variants->variants_json, true)['image']){
                    \Storage::disk('public')->delete(substr(json_decode($variants->variants_json, true)['image'], 8));
                }
                $image_paths = Image::where('product_id', $product->id)->where('variant_id', json_decode($variants->variants_json, true)['id']);
                if ($image_paths){
                    foreach ($image_paths as $image_psths) {
                        \Storage::disk('public')->delete(substr($image_psths->path, 8));
                    }
                }
                return redirect()->route('admin.products.index');
            }
        }

    }
}
