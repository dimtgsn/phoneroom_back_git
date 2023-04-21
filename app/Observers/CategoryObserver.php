<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    public function created() {
        Cache::forget('category');
    }
}
