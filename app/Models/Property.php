<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $table = 'properties';

    protected $fillable = [
        'product_id',
        'category_id',
        'properties_json',
    ];

    protected $casts = [
        'properties_json' => 'array'
    ];
}
