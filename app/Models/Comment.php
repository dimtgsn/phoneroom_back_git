<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'author',
        'type',
        'advantages',
        'disadvantages',
        'comment',
        'rating',
        'product_id',
        'user_id',
        'answer_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

