<?php

namespace App\Http\Resources\User;

use App\Models\Profile;
use Illuminate\Http\Resources\Json\JsonResource;

class   UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'phone' => $this->phone,
            'email' => $this->email ?? '',
            'middle_name' => $this->profile->middle_name ?? '',
            'last_name' =>$this->profile->last_name ?? '',
            'address' => $this->profile->address->fullAddress ?? '',
            'messages' => $this->messages ?? '',
            'orders' => $this->orders()->limit(8)->orderBy('created_at', 'DESC')->get() ?? [],
        ];
    }
}
