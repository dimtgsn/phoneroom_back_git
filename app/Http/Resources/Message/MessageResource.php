<?php

namespace App\Http\Resources\Message;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'id' => $this->id,
            'theme' => $this->theme,
            'message' => $this->message,
        ];
    }
}
