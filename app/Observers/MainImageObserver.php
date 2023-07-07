<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;

class MainImageObserver
{
    public function created() {
        Cache::forget('main_image');
    }
}
