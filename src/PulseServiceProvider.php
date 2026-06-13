<?php

namespace Vjects\Pulse;

use Illuminate\Support\ServiceProvider;

class PulseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('vjects-pulse', function ($app) {
            return new PulseManager();
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'v-pulse');
        
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Vjects\Pulse\Commands\ScanRoutesCommand::class,
            ]);
        }
    }
}
