<?php

namespace App\Http\Controllers\Api\PromotionImage;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromotionImage\PromotionImageCollection;
use App\Models\PromotionImage;

class PromotionImageController  extends Controller
{

    public function index(){

        $images = PromotionImage::orderBy('position', 'ASC')->get();
        return new PromotionImageCollection($images);
    }

}