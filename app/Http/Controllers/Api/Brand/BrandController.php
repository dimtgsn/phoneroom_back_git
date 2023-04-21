<?php

namespace App\Http\Controllers\Api\Brand;

use App\Http\Controllers\Controller;
use App\Http\Requests\Brand\StoreRequest;
use App\Http\Requests\Brand\UpdateRequest;
use App\Http\Resources\Brand\BrandCollection;
use App\Http\Resources\Brand\BrandResource;
use App\Models\Brand;
use App\Models\Category;
use App\Services\Brand\Service;

class BrandController extends Controller
{
    public function index(){

        $brands = Brand::orderBy('id')
            ->get();

        return new BrandCollection($brands);
    }

}
