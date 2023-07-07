<?php

namespace App\Http\Resources\Comment;

use App\Utilities\DateFormatting;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'user' => ($this->user->profile->last_name.' ' ?? '').$this->user->first_name.(' '.$this->user->profile->middle_name ?? ''),
            'user_id' => $this->user_id,
            'comment' => $this->comment,
            'advantages' => $this->advantages,
            'disadvantages' => $this->disadvantages,
            'rating' => $this->rating,
            'can_update' => $this->type === 0  && Carbon::now()->diffInDays(Carbon::parse($this->created_at)) < 1,
            'created_at' => DateFormatting::format($this->created_at),
//            'type' => $this->type,
            'answer_id' => $this->answer_id,
        ];
    }
}
