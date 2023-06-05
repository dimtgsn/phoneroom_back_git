<?php

namespace App\Http\Controllers\Admin\Product;

use App\Console\Commands\DeleteIndexCommand;
use App\Console\Commands\ImportAndUpdateIndexCommand;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryVariantRatingDesc;
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
use Illuminate\Support\Facades\Artisan;
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

        $this->import_and_update_indexes();
        return redirect()->route('admin.products.index');
    }

    public function show(Product $product)
    {
        $variant = [];
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
        Artisan::call(ImportAndUpdateIndexCommand::class, [
            'model' => "App\Models\CategoryVariantRatingDesc"
        ]);
        $this->import_and_update_indexes();
        return redirect()->route('admin.products.index');
    }

    public function variant_update(Product $product, $variant_slug, UpdateRequest $request, Service $service){

        $data = $request->validated();
        $data['published'] = isset($data['published']) ? (bool)$data['published']:false;

        $service->variant_update($data, $product, $variant_slug);

        $this->import_and_update_indexes();
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
        if (count($product->variants)){
            foreach (Variant::where('product_id', $product->id) as $v){
                $v->delete();
            }
        }
        if($product->image){
            \Storage::disk('public')->delete(substr($product->image, 8));
        }
        if($product->images){
            foreach ($product->images as $image) {
                \Storage::disk('public')->delete(substr($image->path, 8));
            }
        }

        $product->delete();
        $this->delete_data_indexes();
        $this->import_and_update_indexes();
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

                if(json_decode($variants->variants_json, true)['image']){
                    $flag = false;
                    foreach ($product->variants as $v){
                        if(json_decode($variants->variants_json, true)['id'] !== json_decode($v->variants_json, true)['id'] &&
                            json_decode($variants->variants_json, true)['image'] === json_decode($v->variants_json, true)['image']){
                            $flag = true;
                            break;
                        }
                    }
                    if (!$flag){
                        \Storage::disk('public')->delete(substr(json_decode($variants->variants_json, true)['image'], 8));
                    }
                }
                $image_paths = Image::where('product_id', $product->id)->where('variant_id', json_decode($variants->variants_json, true)['id']);
                if ($image_paths){
                    $flag = false;
                    foreach ($image_paths as $image_path) {
                        foreach ($product->variants as $v){
                            if(json_decode($variants->variants_json, true)['id'] !== json_decode($v->variants_json, true)['id']){
                                $images = Image::where('product_id', $product->id)->where('variant_id', json_decode($v->variants_json, true)['id']);
                                foreach ($images as $image) {
                                    if($image_path->path === $image->path){
                                        $flag = true;
                                        break;
                                    }
                                }
                            }
                        }

                        if (!$flag){
                            \Storage::disk('public')->delete(substr($image_path->path, 8));
                        }
                    }
                }

                if (count($product->variants) === 1){
                    if($product->image){
                        \Storage::disk('public')->delete(substr($product->image, 8));
                    }
                    if($product->images){
                        foreach ($product->images as $image) {
                            \Storage::disk('public')->delete(substr($image->path, 8));
                        }
                    }
                    Option::where('product_id', $product->id)->first()->delete();
                    $product->delete();
                }
                else{
                    $options_json = json_decode($product->option->options_json, true);
                    for ($i=0;$i<count($options_json);$i++){
                        for ($j=0;$j<count($options_json[$i]['values']);$j++){
                            if(isset($options_json[$i]['values'][$j]) &&
                                $options_json[$i]['values'][$j] === json_decode($variants->variants_json, true)['name']){
                                unset($options_json[$i]['values'][$j]);
                            }
                        }
                    }

                    Option::where('product_id', $product->id)->first()->update([
                        'options_json' => json_encode($options_json, JSON_UNESCAPED_UNICODE),
                    ]);
                    Variant::where('variants_json', json_encode($variants->variants_json, true))->delete();
                }
            }
        }
        $this->delete_data_indexes();
        $this->import_and_update_indexes();
        return redirect()->route('admin.products.index');
    }

    public function delete_data_indexes()
    {
        Artisan::call(DeleteIndexCommand::class, [
            'model' => "App\Models\CategoryVariantRatingDesc"
        ]);
        Artisan::call(DeleteIndexCommand::class, [
            'model' => "App\Models\CategoryVariantPriceDesc"
        ]);
        Artisan::call(DeleteIndexCommand::class, [
            'model' => "App\Models\CategoryVariantCreatedAtDesc"
        ]);
        Artisan::call(DeleteIndexCommand::class, [
            'model' => "App\Models\CategoryVariantPriceAsc"
        ]);
    }

    public function import_and_update_indexes()
    {
        Artisan::call(ImportAndUpdateIndexCommand::class, [
            'model' => "App\Models\CategoryVariantRatingDesc"
        ]);
        Artisan::call(ImportAndUpdateIndexCommand::class, [
            'model' => "App\Models\CategoryVariantPriceDesc"
        ]);
        Artisan::call(ImportAndUpdateIndexCommand::class, [
            'model' => "App\Models\CategoryVariantCreatedAtDesc"
        ]);
        Artisan::call(ImportAndUpdateIndexCommand::class, [
            'model' => "App\Models\CategoryVariantPriceAsc"
        ]);
    }
}
