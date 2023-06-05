<?php

namespace App\Http\Controllers\Admin\BannerImage;

use App\Http\Controllers\Controller;
use App\Http\Requests\MainImage\StoreRequest;
use App\Http\Requests\MainImage\UpdateRequest;
use App\Models\BannerImage;
use App\Services\BannerImage\Service;

class BannerImageController  extends Controller
{

    public function index(){

        $images = BannerImage::select()->orderBy('position', 'ASC')->get();
        return view('admin.images.banner.index', compact('images'));
    }

    public function create(){

        return view('admin.images.banner.create');
    }

    public function store(StoreRequest $request, Service $service){
        $data = $request->validated();
        $service->create($data);
        return redirect()->route('admin.banner_images.index');
    }

    public function edit(){
        $images = BannerImage::select()->orderBy('position', 'ASC')->get();
        $positonLast = BannerImage::count();
        return view('admin.images.banner.edit', compact('images', 'positonLast'));
    }

    public function update(UpdateRequest $request, Service $service){
        $data = $request->validated();
        $service->update($data);
        return redirect()->route('admin.banner_images.index');
    }

    public function destroy(BannerImage $banner_image){
        \Storage::disk('public')->delete(substr($banner_image->path, 8));
        $banner_image->delete();
        return redirect()->route('admin.banner_images.index');
    }
}