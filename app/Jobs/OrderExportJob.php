<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Order\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Service $service)
    {
        $myWarehouseNewOrderId = $service->export_order($this->order, new \App\Services\MyWarehouse\Service);
        SendOrderEmailJob::dispatch($this->order)->onQueue('emails');
        SendTelegramNotificationJob::dispatch($this->order, $myWarehouseNewOrderId)->onQueue('telegrams');
    }
}
