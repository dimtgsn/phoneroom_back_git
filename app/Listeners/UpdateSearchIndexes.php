<?php

namespace App\Listeners;

use App\Console\Commands\ImportAndUpdateIndexCommand;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;

class UpdateSearchIndexes
{

    public function handle(Logout $event)
    {
        Artisan::call(ImportAndUpdateIndexCommand::class, [
            'model' => "App\Models\CategoryVariantRatingDesc"
        ]);
        Artisan::call(ImportAndUpdateIndexCommand::class, [
            'model' => "App\Models\CategoryVariantPriceDesc"
        ]);
        Artisan::call(ImportAndUpdateIndexCommand::class, [
            'model' => "App\Models\CategoryVariantCreatedAtDesc"
        ]);
        Artisan::call(ImportAndUpdateIndexCommand::class, [
            'model' => "App\Models\CategoryVariantPriceAsc"
        ]);
    }
}
