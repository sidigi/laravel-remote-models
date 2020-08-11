<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Routing\RoutingServiceProvider;
use Illuminate\Support\ServiceProvider;

class LaravelRemoteModelsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        app(RoutingServiceProvider::class)->boot();
    }

    public function register()
    {
    }
}
