<?php

namespace Sidigi\LaravelRemoteModels\Services;

use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;

class ClientRegistrator
{
    private Application $app;
    private array $options;
    private string $client;

    public function __construct(Application $app, array $options)
    {
        $this->app = $app;
        $this->options = $options;

        $this->client = $this->options['client'];
    }

    public function register()
    {
        $this->options['pagination_strategy'] = $this->options['pagination_strategy'] ?? $$this->defaultPaginationStrategy();

        $this->validateClient();

        if (app()->bound($this->client)) {
            return;
        }

        $this->app->bind(
            $this->client,
            function () {
                $provider = $this->getProvider();
                $responseKey = $this->getResponseKey();

                $clientObj = new ($this->client)(
                    $this->app->make($provider['request_class']),
                    $responseKey
                );

                $clientObj->baseUrl($this->options['base_uri'] ?? '');

                $paginationStrategy = $this->getPaginationStrategy();

                $clientObj->setPaginationStrategy(
                    $paginationStrategy['class'],
                    $paginationStrategy['response_number_key'],
                    $paginationStrategy['defaults']
                );

                //set aws function
                if ($this->options['function_name'] ?? false) {
                    $clientObj->functionName($this->options['function_name']);
                }

                return $clientObj;
            }
        );
    }

    public function defaultResponseKey() : string
    {
        return  config('laravel-remote-models.defaults.response_key', '');
    }

    public function defaultPaginationStrategy() : string
    {
        return  config('laravel-remote-models.defaults.pagination_strategy', '');
    }

    public function getProvider()
    {
        return config("laravel-remote-models.providers.{$this->options['provider']}");
    }

    public function getPaginationStrategy()
    {
        return config("laravel-remote-models.pagination_strategies.{$this->options['pagination_strategy']}");
    }

    public function getResponseKey()
    {
        return isset($this->options['response_key']) ? $this->options['response_key'] : $this->defaultResponseKey();
    }

    private function validateClient()
    {
        if (! $this->options['client']) {
            throw new InvalidArgumentException('client key must be set for client');
        }

        if (empty($this->options['provider'])) {
            throw new InvalidArgumentException('provider must be set for client');
        }

        if (empty($this->options['base_uri'])) {
            throw new InvalidArgumentException('base_uri must be set for client');
        }

        if ($this->getProvider()['request_class'] === AwsLambdaPendingRequest::class) {
            if (empty($this->options['function_name'])) {
                throw new InvalidArgumentException('function_name must be set for client');
            }
        }
    }
}
