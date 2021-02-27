<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\ServiceProvider;
use Sidigi\LaravelRemoteModels\Clients\AwsLambda\PendingRequest as AwsLambdaPendingRequest;
use Sidigi\LaravelRemoteModels\Mixins\HelperRequestMixin;
use Sidigi\LaravelRemoteModels\Mixins\JsonApiRequestMixin;
use Sidigi\LaravelRemoteModels\Services\ClientRegistrator;

class LaravelRemoteModelsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPublishables();
        $this->registerFacades();
        $this->registerRequestMacro();
        $this->registerClients();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-remote-models.php', 'laravel-remote-models');
    }

    protected function registerPublishables() : self
    {
        $this->publishes([
            __DIR__.'/../config/laravel-remote-models.php' => config_path('laravel-remote-models.php'),
        ], 'config');

        return $this;
    }

    public function registerFacades()
    {
        $this->app->bind('aws-lambda', function () {
            return resolve(AwsLambdaPendingRequest::class);
        });
    }

    protected function registerClients()
    {
        collect(config('laravel-remote-models.clients', []))->each(function ($clientOptions, $clientName) {
            (new ClientRegistrator($this->app, $clientName, $clientOptions))->register();
        });
    }

    public function registerRequestMacro()
    {
        PendingRequest::mixin(new HelperRequestMixin());
        PendingRequest::mixin(new JsonApiRequestMixin());
    }
}
