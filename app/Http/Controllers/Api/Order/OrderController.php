<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreRequest;
use App\Http\Requests\Order\ZipCheckRequest;
use App\Http\Resources\Order\OrderCollection;
use App\Models\MyWarehouse;
use App\Models\User;
use App\Notifications\Telegram;
use App\Services\Order\Service;

class OrderController  extends Controller
{
    // TODO проверить уведомление в telegram
    public function create(StoreRequest $request, Service $service, \App\Services\MyWarehouse\Service $service_myWarehouse){
        $data = $request->validated();
        if(!$this->zip_check($data['Zip'])){
            return $this->zip_check($data['Zip']);
        }
        $new_order = $service->create($data);
        if ($new_order === false){
            abort(500);
        }
        else{
            $user = User::where('position_id', 3)->first();
            $myWarehouse = MyWarehouse::select('token')->first();
            $file = $service_myWarehouse->createExportFile($myWarehouse, $new_order['myWarehouseNewOrderId']);
            ['uri' => $filepath] = stream_get_meta_data(tmpfile());
            file_put_contents($filepath, $file);
            if (ob_get_level()) {
                ob_end_clean();
            }
            $user->notify(new Telegram($new_order['order'], $filepath));
        }
        return (int)$new_order['order']->id;
    }

    public function zip_check($zip){
        $token = env('BOXBERRY_TOKEN', 'c52a8b8d9b704226ececde2a40b50dfa');
        $url='https://api.boxberry.ru/json.php?token='.$token.'&method=ZipCheck&Zip='.$zip;
        $handle = fopen($url, "rb");
        $contents = stream_get_contents($handle);
        fclose($handle);
        $res = json_decode($contents,true);
        if(isset($res['err'])){
            return false;
        }
        return true;
    }

    public function index(User $user, Service $order_service){

        $orders = $user->orders()->orderBy('created_at', 'DESC')->get();
        foreach ($orders as $i => $order){
            $orders[$i]['products'] = $order_service->get_order_products($order, false)['products'];
        }
        return new OrderCollection($orders);
    }
}