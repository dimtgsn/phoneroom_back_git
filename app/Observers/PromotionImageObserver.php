<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;

class PromotionImageObserver
{
    public function created() {
        Cache::forget('promotion_image');
    }
}
