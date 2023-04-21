<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;


    protected $table = 'options';

    protected $fillable = [
        'product_id',
        'options_json',
    ];

    protected $casts = [
        'options_json' => 'array'
    ];
}
