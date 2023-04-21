<?php

namespace App\Http\Controllers\Admin\Brand;

use App\Http\Controllers\Controller;
use App\Http\Requests\Brand\StoreRequest;
use App\Http\Requests\Brand\UpdateRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Services\Brand\Service;
use App\Utilities\ImageConvertToWebp;

class BrandController extends Controller
{
    public function index(){

        $brands = Brand::orderBy('id')->with('categories')->paginate(101);

        return view('admin.brand.index', compact('brands'));
    }

    public function create(){

        $categories = Category::all();
        return view('admin.brand.create', compact('categories'));
    }

    public function store(StoreRequest $request, Service $service){


        $data = $request->validated();

        $service->store($data);
        return redirect()->route('admin.brands.index');
    }

    public function show(Brand $brand)
    {

        return view('admin.brand.show', compact('brand'));
    }

    public function edit(Brand $brand)
    {
        $brands = Brand::all();
        $categories = Category::all();

        return view('admin.brand.edit', compact('brand', 'categories', 'brands'));
    }

    public function update(Brand $brand, UpdateRequest $request, Service $service){

        $data = $request->validated();

        $service->update($data, $brand);
        return redirect()->route('admin.brands.index');
    }

    public function destroy(Brand $brand){
        $brand->delete();
        if($brand->image){
            \Storage::disk('public')->delete(substr($brand->image, 8));
        }
        return redirect()->route('admin.brands.index');
    }
}
