<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreRequest;
use App\Http\Requests\Order\ZipCheckRequest;
use App\Services\Order\Service;

class OrderController  extends Controller
{

    public function create(StoreRequest $request, Service $service){
        $data = $request->validated();
        return $service->create($data);
    }

    public function zip_check(ZipCheckRequest $request){
        $data = $request->validated();
        $token = env('BOXBERRY_TOKEN', '');
        $url='https://api.boxberry.ru/json.php?token='.$token.'&method=ZipCheck&Zip='.$data['Zip'];
        $handle = fopen($url, "rb");
        $contents = stream_get_contents($handle);
        fclose($handle);
        $res = json_decode($contents,true);
        if(isset($res['err'])){
            return 0;
        }
        return 1;
    }

}