<?php

namespace App\Jobs;

use App\Models\MyWarehouse;
use App\Models\Order;
use App\Models\User;
use App\Notifications\Telegram;
use App\Services\MyWarehouse\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTelegramNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    protected $myWarehouseNewOrderId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order, $myWarehouseNewOrderId)
    {
        $this->order = $order;
        $this->myWarehouseNewOrderId = $myWarehouseNewOrderId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Service $service)
    {
        // TODO удалить строку ниже
//        $new_order['myWarehouseNewOrderId'] = '90fad5de-1f3e-11ee-0a80-114a0074d6fd';
        $user = User::where('position_id', 3)->first();
        $myWarehouse = MyWarehouse::select('token')->first();
        $file = $service->createExportFile($myWarehouse, $this->myWarehouseNewOrderId);
        ['uri' => $filepath] = stream_get_meta_data(tmpfile());
        file_put_contents($filepath, $file);
        if (ob_get_level()) {
            ob_end_clean();
        }
        $user->notify(new Telegram($this->order, $filepath));
    }
}
