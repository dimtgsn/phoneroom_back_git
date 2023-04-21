<?php

namespace App\Http\Controllers\Admin\PromotionImage;

use App\Http\Controllers\Controller;
use App\Http\Requests\MainImage\StoreRequest;
use App\Http\Requests\MainImage\UpdateRequest;
use App\Models\PromotionImage;
use App\Services\PromotionImage\Service;

class PromotionImageController  extends Controller
{

    public function index(){

        $images = PromotionImage::select()->orderBy('position', 'ASC')->get();
        return view('admin.images.promotion.index', compact('images'));
    }

    public function create(){

        return view('admin.images.promotion.create');
    }

    public function store(StoreRequest $request, Service $service){
        $data = $request->validated();
        $service->create($data);
        return redirect()->route('admin.promotion_images.index');
    }

    public function edit(){
        $images = PromotionImage::select()->orderBy('position', 'ASC')->get();
        $positonLast = PromotionImage::count();
        return view('admin.images.promotion.edit', compact('images', 'positonLast'));
    }

    public function update(UpdateRequest $request, Service $service){
        $data = $request->validated();
        $service->update($data);
        return redirect()->route('admin.promotion_images.index');
    }
}