<?php

namespace App\Http\Controllers\Admin\MyWarehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\MyWarehouse\ConnectRequest;
use App\Http\Requests\MyWarehouse\ExportRequest;
use App\Jobs\ProductExportJob;
use App\Models\MyWarehouse;
use App\Models\Product;
use App\Services\MyWarehouse\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        foreach ($data['ids'] as $id => $value){
            $data['ids'] = [$id => $value,];
            ProductExportJob::dispatch($data)->onConnection('redis-long-processes')->onQueue('exports');
        }
        return redirect()->route('admin.my-warehouse.main');
    }

    public function webhooks(Request $request, Service $service){
        $data = $request->validate([
            "auditContext" => ['required'],
            "events" => ['required'],
            "requestId" => ['required', 'string'],
        ]);

        if (count($data['events']) > 1){
            foreach ($data['events'] as $event) {
                $entity = $event['meta']['type'];
                $action = $event['action'];
                $updated_fields = $event['updatedFields'] ?? [];
                $my_warehouse_id = substr($event['meta']['href'], -36, 36);
                $service->import($entity, $action, $my_warehouse_id, $updated_fields);
            }
        }
        else{
            $entity = $data['events'][0]['meta']['type'];
            $action = $data['events'][0]['action'];
            $updated_fields = $data['events'][0]['updatedFields'] ?? [];
            $my_warehouse_id = substr($data['events'][0]['meta']['href'], -36, 36);
            $service->import($entity, $action, $my_warehouse_id, $updated_fields);
        }


//        Log::info(json_encode([
//            'entity' => $entity,
//            'action' => $action,
//            'my_warehouse_id' => $my_warehouse_id,
//        ], JSON_UNESCAPED_UNICODE));

//        $myWarehouse = MyWarehouse::select('token')->first();
    }


}