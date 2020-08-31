<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Sidigi\LaravelRemoteModels\Exceptions\PaginationStrategyNotFoundException;
use Sidigi\LaravelRemoteModels\JsonApi\Pagination\Contracts\PaginationStrategyContract;

class LaravelRemoteModelsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPublishables();
        $this->registerDefaultPaginationStrategy();
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

    protected function registerDefaultPaginationStrategy()
    {
        $default = config('laravel-remote-models.defaults.pagination_strategy');
        $paginationStrategy = config("laravel-remote-models.pagination_strategies.$default");

        if (! class_exists($paginationStrategy['class'])) {
            throw new PaginationStrategyNotFoundException("Class {$paginationStrategy['class']} not found");
        }

        $this->app->bind(
            PaginationStrategyContract::class,
            fn () => $this->makeStrategyInstance($paginationStrategy)
        );
    }

    protected function registerClients()
    {
        $clients = config('laravel-remote-models.clients', []);

        collect($clients)->each(function ($clientOptions) {
            $client = $clientOptions['client'] ?? null;

            if (! $client) {
                throw new InvalidArgumentException('client key must be set for client');
            }

            $paginationStrategy = config("laravel-remote-models.pagination_strategies.{$clientOptions['pagination_strategy']}");

            $this->app->bind(
                $client,
                fn () => (new $client(
                    $this->app->make(PendingRequest::class),
                    $this->app->make(UrlManager::class),
                    $this->makeStrategyInstance($paginationStrategy),
                ))->baseUrl($clientOptions['base_uri'] ?? '')
            );
        });
    }

    private function makeStrategyInstance(array $strategy)
    {
        $defaults = $strategy['defaults'] ?? [];
        $defaults['response_number_key'] = $strategy['response_number_key'];

        return $this->app->make(
            $strategy['class'],
            [
                'defaults' => $defaults,
            ]
        );
    }
}
