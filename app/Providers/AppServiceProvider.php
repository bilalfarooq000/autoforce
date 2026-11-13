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
        // Railway's edge terminates TLS and forwards plain HTTP to the container,
        // so without this every generated asset/route URL would come out as
        // http:// on an https:// page — blocked by browsers as mixed content.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
