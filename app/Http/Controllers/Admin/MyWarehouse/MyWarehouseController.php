<?php

namespace App\Http\Controllers\Admin\MyWarehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\MyWarehouse\ConnectRequest;
use App\Http\Requests\MyWarehouse\ExportRequest;
use App\Models\MyWarehouse;
use App\Models\Product;
use App\Services\MyWarehouse\Service;
use Illuminate\Support\Facades\Http;
use Request;

class MyWarehouseController  extends Controller
{

    public function index(){

        return view('admin.my-warehouse.index');
    }

    public function connect(ConnectRequest $request, Service $service){

        $data = $request->validated();
        $response = $service->connect($data);

        if (!$response){
            $error_msg = 'Неверный логин или пароль. Либо ваша сессия подошла к концу.';
            return view('admin.my-warehouse.index', compact('error_msg'));
        }
        else{
            return redirect()->route('admin.my-warehouse.main');
        }
    }

    public function main()
    {
        $products = Product::where('exported', false)->paginate(10);
        return view('admin.my-warehouse.main', compact('products'));
    }

    public function export(ExportRequest $request, Service $service){
        $data = $request->validated();
        $response = $service->export($data);
        dump($response);
        return redirect()->route('admin.my-warehouse.main');
    }

    public function webhook(Request $request){
//        $myWarehouse = MyWarehouse::select('token')->first();
        //        $responseWebhook = Http::withToken($myWarehouse->token)
//            ->withHeaders([
//                "Content-Type" => "application/json"
//            ])
//            ->post('https://online.moysklad.ru/api/remap/1.2/entity/webhook', [
//                "url" => env("APP_URL").'/admin'.'/my-warehouse'.'/webhook',
//                "action" => "CREATE",
//                "entityType" => "product",
//            ]);
//        dd($responseWebhook->body());
        dd($request);
    }


}