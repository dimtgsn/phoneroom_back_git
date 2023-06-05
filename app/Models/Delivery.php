<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
    ];

    public function orders(): BelongsToMany{
        return $this->belongsToMany(Order::class);
    }
}
