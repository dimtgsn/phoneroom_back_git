<?php

namespace App\Http\Controllers\Api\BannerImage;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerImage\BannerImageCollection;
use App\Models\BannerImage;

class BannerImageController  extends Controller
{

    public function index(){

        $images = BannerImage::orderBy('position', 'ASC')->get();
        return new BannerImageCollection($images);
    }

}