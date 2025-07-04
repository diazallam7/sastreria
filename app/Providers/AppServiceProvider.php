<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Forzar HTTPS en todas las URLs generadas (incluyendo assets)
        //if ($this->app->environment('production') || $this->app->environment('local')) {
          //  URL::forceScheme('https');
        //}
        
        // Opcional: Si necesitas especificar el dominio (útil para ngrok)
        // URL::forceRootUrl(config('app.url'));
    }
}