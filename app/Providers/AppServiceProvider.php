<?php

namespace App\Providers;

use App\Helpers\DateHelper;
use App\Services\Nfce\NfceScraperResolver;
use App\Services\Nfce\NfceSourceResolver;
use App\Services\Nfce\Scrapers\SvrsNfceScraper;
use App\View\Composers\AppLayoutComposer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->when(SvrsNfceScraper::class)
            ->needs('$httpConfig')
            ->giveConfig('services.nfce.http');

        $this->app->tag([SvrsNfceScraper::class], 'nfce.scrapers');

        $this->app->when(NfceScraperResolver::class)
            ->needs('$scrapers')
            ->giveTagged('nfce.scrapers');

        $this->app->when(NfceSourceResolver::class)
            ->needs('$sources')
            ->giveConfig('services.nfce.sources');

        $this->app->when(NfceSourceResolver::class)
            ->needs('$ufCodes')
            ->giveConfig('services.nfce.uf_codes');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.layouts.app', AppLayoutComposer::class);

        // Garante que componentes nas subpastas possam ser chamados sem o prefixo (retrocompatibilidade)
        Blade::anonymousComponentPath(resource_path('views/components/ui'));
        Blade::anonymousComponentPath(resource_path('views/components/form'));
        Blade::anonymousComponentPath(resource_path('views/components/layout'));
        Blade::anonymousComponentPath(resource_path('views/components/nav'));

        Model::preventLazyLoading(! app()->isProduction());

        Carbon::setLocale(config('app.locale'));
        setlocale(LC_ALL, config('app.locale').'.UTF-8');
        date_default_timezone_set(config('app.timezone'));
        Number::useCurrency(config('app.currency'));
        Number::useLocale(config('app.locale'));

        require_once app_path('Helpers/DateHelper.php');
        require_once app_path('Helpers/CurrencyHelper.php');
        require_once app_path('Helpers/DocumentHelper.php');

        Carbon::macro('formatDate', function () {
            return DateHelper::format($this);
        });

        Carbon::macro('formatShort', function () {
            return DateHelper::formatShort($this);
        });

        Carbon::macro('formatDateTime', function () {
            return DateHelper::formatDateTime($this);
        });

        Carbon::macro('formatRelative', function () {
            return DateHelper::formatRelative($this);
        });
    }
}
