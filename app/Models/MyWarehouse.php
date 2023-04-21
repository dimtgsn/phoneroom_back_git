<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MyWarehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'token'
    ];
}
