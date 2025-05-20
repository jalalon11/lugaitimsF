<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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

            // Force acceptance of commands in production without confirmation
            $this->app->bind('Illuminate\Console\ConfirmableTrait', function () {
                return new class {
                    protected function confirmToProceed()
                    {
                        return true;
                    }
                };
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for database schema
        Schema::defaultStringLength(191);

        // Enable strict mode for all models
        Model::shouldBeStrict(!app()->isProduction());

        // Log model not found exceptions instead of throwing them
        Model::handleLazyLoadingViolationUsing(function($model, $relation) {
            Log::warning("Attempted to lazy load [{$relation}] on model [" . get_class($model) . "]");
        });

        // Handle missing relations gracefully
        Model::handleMissingAttributeViolationUsing(function($model, $key) {
            Log::warning("Attempted to access missing attribute [{$key}] on model [" . get_class($model) . "]");
            return null;
        });
    }
}
