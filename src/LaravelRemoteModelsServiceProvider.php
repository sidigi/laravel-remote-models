<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Support\ServiceProvider;

class LaravelRemoteModelsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPublishables();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-remote-models.php', 'laravel-remote-models');
    }

    protected function registerPublishables() : self
    {
        if (! $this->app->runningInConsole()) {
            return $this;
        }
        $this->publishes([
            __DIR__.'/../config/laravel-remote-models.php' => config_path('laravel-remote-models.php'),
        ], 'config');

        return $this;
    }
}
