<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status_id',
        'ship_address',
        'description',
        'zip',
        'total'
    ];

    protected $with = [
        'user',
        'status',
        'delivery',
    ];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo{
        return  $this->BelongsTo(OrderStatus::class);
    }

    public function delivery(): BelongsTo{
        return  $this->BelongsTo(Delivery::class);
    }
}
