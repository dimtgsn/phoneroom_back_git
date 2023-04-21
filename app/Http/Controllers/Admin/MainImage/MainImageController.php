<?php

namespace App\Http\Controllers\Admin\MainImage;

use App\Http\Controllers\Controller;
use App\Http\Requests\MainImage\StoreRequest;
use App\Http\Requests\MainImage\UpdateRequest;
use App\Models\MainImage;
use App\Services\MainImage\Service;

class MainImageController  extends Controller
{

    public function index(){

        $images = MainImage::select()->orderBy('position', 'ASC')->get();
        return view('admin.images.main.index', compact('images'));
    }

    public function create(){

        return view('admin.images.main.create');
    }

    public function store(StoreRequest $request, Service $service){
        $data = $request->validated();
        $service->create($data);
        return redirect()->route('admin.main_images.index');
    }

    public function edit(){
        $images = MainImage::select()->orderBy('position', 'ASC')->get();
        $positonLast = MainImage::count();
        return view('admin.images.main.edit', compact('images', 'positonLast'));
    }

    public function update(UpdateRequest $request, Service $service){
        $data = $request->validated();
        $service->update($data);
        return redirect()->route('admin.main_images.index');
    }
}