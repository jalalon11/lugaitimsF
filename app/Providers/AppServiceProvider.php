<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // In production, suppress deprecation warnings
        if (app()->environment('production')) {
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
