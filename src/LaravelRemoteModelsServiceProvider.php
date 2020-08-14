<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class LaravelRemoteModelsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPublishables();
        $this->registerClients();
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

    protected function registerClients()
    {
        $clients = config('laravel-remote-models.clients', []);

        collect($clients)->each(function ($clientOptions, $key) {
            $client = $clientOptions['client'] ?? null;

            if (! $client) {
                throw new InvalidArgumentException('client key must be set for client');
            }

            //check instance of client

            if ($client) {
                $this->app->bind($client, function () use ($client, $clientOptions) {
                    return (new $client(
                        $this->app->make(PendingRequest::class),
                        $this->app->make(UrlManager::class),
                        $clientOptions['base_uri'] ?? null
                    ))->fromResponseKey(config('laravel-remote-models.options.response_key', 'data'));
                });
            }
        });
    }
}
