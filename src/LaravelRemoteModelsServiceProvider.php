<?php

namespace Sidigi\LaravelRemoteModels;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Sidigi\LaravelRemoteModels\JsonApi\Pagination\Contracts\PaginationStrategyContract;

class LaravelRemoteModelsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPublishables();

        $default = config('laravel-remote-models.defaults.pagination_strategy');
        $strategy = config("laravel-remote-models.pagination_strategies.$default");
        $this->registerPaginationStrategies();
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

    protected function registerPaginationStrategies()
    {
        $default = config('laravel-remote-models.defaults.pagination_strategy');
        $strategy = config("laravel-remote-models.pagination_strategies.$default");

        if (! class_exists($strategy['class'])) {
            throw new Exception("Class {$strategy['class']} not found");
        }

        $this->app->bind(PaginationStrategyContract::class, function ($app) use ($strategy) {
            return $app->make($strategy['class'], ['defaults' => $strategy['defaults'] ?? []]);
        });
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
                $this->app->bind($client, function ($app) use ($client, $clientOptions) {
                    $strategy = config("laravel-remote-models.pagination_strategies.{$clientOptions['pagination_strategy']}");

                    return (new $client(
                        $app->make(PendingRequest::class),
                        $app->make(UrlManager::class),
                        $app->make($strategy['class'], ['defaults' => $strategy['defaults'] ?? []])
                    ))
                        ->baseUrl($clientOptions['base_uri'] ?? '');
                });
            }
        });
    }
}
