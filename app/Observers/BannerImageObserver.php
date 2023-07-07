<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;

class BannerImageObserver
{
    public function created() {
        Cache::forget('banner_image');
    }
}
