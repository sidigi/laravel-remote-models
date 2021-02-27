<?php

namespace Sidigi\LaravelRemoteModels\Services;

use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;
use Sidigi\LaravelRemoteModels\Providers\AwsLambdaProvider;
use Sidigi\LaravelRemoteModels\Providers\ProviderInterface;

class ClientRegistrator
{
    private Application $app;
    private array $options;
    private string $clientName;
    private string $clientClass;
    private string $paginationStrategyName;

    public function __construct(Application $app, string $clientName, array $options)
    {
        $this->app = $app;
        $this->clientName = $clientName;
        $this->options = $options;

        $this->clientClass = $this->options['client'];
        $this->paginationStrategyName = $this->options['pagination_strategy'] ?? $this->defaultPaginationStrategy();
    }

    public function register()
    {
        $this->validateClient();

        if (app()->bound($this->clientClass)) {
            return;
        }

        $this->app->bind(
            $this->clientClass,
            function () {
                $clientObj = new ($this->clientClass)(
                    $this->getProvider()->request(),
                    $this->getResponseKey(),
                    $this->getPaths()
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

        $this->app->alias($this->clientClass, $this->clientName);
    }

    public function defaultResponseKey() : string
    {
        return  config('laravel-remote-models.defaults.response_key', '');
    }

    public function defaultPaginationStrategy() : string
    {
        return  config('laravel-remote-models.defaults.pagination_strategy', '');
    }

    public function getProvider() : ProviderInterface
    {
        return resolve(config("laravel-remote-models.providers.{$this->options['provider']}.class"));
    }

    public function getPaths() : array
    {
        return $this->options['paths'] ?? [];
    }

    public function getPaginationStrategy()
    {
        return config("laravel-remote-models.pagination_strategies.{$this->paginationStrategyName}");
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

        if ($this->getProvider() instanceof AwsLambdaProvider) {
            if (empty($this->options['function_name'])) {
                throw new InvalidArgumentException('function_name must be set for client');
            }
        }
    }
}
