<?php

namespace App\Http\Controllers\Api\MainImage;

use App\Http\Controllers\Controller;
use App\Http\Resources\MainImage\MainImageCollection;
use App\Models\MainImage;

class MainImageController  extends Controller
{

    public function index(){

        $images = MainImage::select('path')->orderBy('position', 'ASC')->get();
        return new MainImageCollection($images);
    }

}