<?php

namespace App\Http\Controllers\Api\Brand;

use App\Http\Controllers\Controller;
use App\Http\Resources\Brand\BrandCollection;
use App\Models\Brand;

class BrandController extends Controller
{
    public function index(){

        $brands = Brand::orderBy('id')
            ->get();

        return new BrandCollection($brands);
    }

}
