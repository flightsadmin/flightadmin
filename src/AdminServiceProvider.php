<?php

namespace Flightsadmin\Flightadmin;

use Flightsadmin\Flightadmin\Commands\Install;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('flightadmin.php'),
            ], 'config');

            $this->commands([
                Install::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'flightadmin');

        // Register the main class to use with the facade
        $this->app->singleton('flightadmin', function () {
            return new Flightadmin;
        });
    }
}
