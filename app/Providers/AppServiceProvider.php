<?php

namespace App\Providers;

use App\Http\Kernel;
use Carbon\CarbonInterval;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::defaultView('vendor.pagination.bootstrap-4');

        Model::shouldBeStrict(!app()->isProduction());

        if (app()->isProduction()){
            DB::whenQueryingForLongerThan(CarbonInterval::seconds(5), function (Connection $connection, QueryExecuted $event) {
                logger()
                    ->channel('telegram')
                    ->debug('whenQueryingForLongerThan:' . $connection->totalQueryDuration());
            });

            DB::listen(function ($query) {
                if ($query->time > 100) {
                    logger()
                        ->channel('telegram')
                        ->debug('whenQueryingForLongerThan:' . $query->sql, $query->bindings);
                }
            });

            $kernel = app(Kernel::class);

            $kernel->whenRequestLifecycleIsLongerThan(
                CarbonInterval::seconds(4),
                function () {
                    logger()
                        ->channel('telegram')
                        ->debug('whenRequestLifecycleIsLongerThan:' . request()->url);
                }
            );

        }

        Validator::extend('phone', function($attribute, $value, $parameters, $validator) {
            $value = preg_replace('/\D/','', $value);
            return !preg_match("/[\D]/", $value) && strlen($value) >= 10;
        });
//        ^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$
        Validator::replacer('phone', function($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute',$attribute, ':attribute is invalid phone number');
        });

        Validator::extend('injection', function($attribute, $value, $parameters, $validator) {
            return !(preg_match('/\bdrop\b/', strtolower($value))
                || preg_match('/\bdatabase\b/', strtolower($value))
                || preg_match('/\bselect\b/', strtolower($value))
                || preg_match('/\bfrom\b/', strtolower($value))
                || preg_match('/\bwhere\b/', strtolower($value))
                || preg_match('/\bdelete\b/', strtolower($value))
                || preg_match('/\binsert\b/', strtolower($value))
                || preg_match('/\bupdate\b/', strtolower($value))
                || preg_match('/\bgroup\b/', strtolower($value))
                || preg_match('/\bopder\b/', strtolower($value))
                || preg_match('/\bby\b/', strtolower($value))
                || preg_match('/\blimit\b/', strtolower($value))
                || preg_match('/\blike\b/', strtolower($value))
                || preg_match('/\b"\b/', strtolower($value))
                || preg_match('/\b\'\b/', strtolower($value))
                || preg_match('/\bdescribe\b/', strtolower($value))
                || preg_match('/\bjoin\b/', strtolower($value))
                || preg_match('/\bview\b/', strtolower($value))
                || preg_match('/\bhaving\b/', strtolower($value))
                || preg_match('/\bbetween\b/', strtolower($value)));
        });

    }
}
