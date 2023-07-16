<?php

namespace App\Jobs;

use App\Mail\Order\OrderCreatedMail;
use App\Models\MyWarehouse;
use App\Models\Order;
use App\Services\MyWarehouse\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

//    public $tries = 5;

    protected $order;
    protected $email;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    // TODO убрать необязательность почты
    public function __construct(Order $order, $email='')
    {
        $this->order = $order;
        $this->email = $email;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Service $service)
    {
//        throw new \Exception("Error", 101);
        // TODO удалить строку ниже
        $new_order['myWarehouseNewOrderId'] = '90fad5de-1f3e-11ee-0a80-114a0074d6fd';
        $myWarehouse = MyWarehouse::select('token')->first();
        $file = $service->createExportFile($myWarehouse, $new_order['myWarehouseNewOrderId']);
        ['uri' => $filepath] = stream_get_meta_data(tmpfile());
        file_put_contents($filepath, $file);
        if (ob_get_level()) {
            ob_end_clean();
        }
        Mail::to('gasanyandmitry@yandex.ru')->send(new OrderCreatedMail($this->order, $filepath));
//        Mail::to($this->email)->send(new OrderCreatedMail($this->order));
    }
}
