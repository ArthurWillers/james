<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        Carbon::setLocale(config('app.locale'));
        setlocale(LC_ALL, config('app.locale').'.UTF-8');
        date_default_timezone_set(config('app.timezone'));
        Number::useCurrency(config('app.currency'));

        require_once app_path('Helpers/DateHelper.php');
    }
}
