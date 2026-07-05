<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Prod / https APP_URL: tum route() ve mutlak URL'ler https uretsin
        // (odeme callback'leri, e-posta linkleri, ICS feed vb. icin kritik).
        if ($this->app->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Prod'da CLI/kuyruk baglaminda (hatirlatma, kampanya) uretilen linkler
        // dogru domain'i kullansin diye kok URL APP_URL'e sabitlenir.
        // Local dev'de istek host'u korunur (port'lu serve calissin).
        if ($this->app->environment('production') && config('app.url')) {
            URL::forceRootUrl(config('app.url'));
        }
    }
}
