<?php

namespace App\Http\Resources\Order;

use App\Models\Order;
use App\Services\Order\Service;
use App\Utilities\DateFormatting;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public static $wrap = '';
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id + 1234,
            'status' => $this->status->id === 5 ? 'В обработке' : $this->status->name,
            'total' => $this->total,
            'created_at' => DateFormatting::format($this->created_at),
            'delivery_date' => $this->delivery_date ? DateFormatting::format($this->delivery_date) : $this->delivery_date,
            'products' => $this->products,
            'delivery' => ($this->delivery->name ?? '').' '.($this->delivery->type ?? ''),
        ];
    }
}
