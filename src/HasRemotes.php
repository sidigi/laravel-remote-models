<?php

namespace Sidigi\LaravelRemoteModels;

use BadMethodCallException;
use Illuminate\Support\Collection;
use Sidigi\LaravelRemoteModels\Exceptions\ClientNotFoundException;

trait HasRemotes
{
    protected function getRemoteClientClass() : string
    {
        if (! $client = config('laravel-remote-models.models.'.static::class)) {
            throw new ClientNotFoundException('Client not found');
        }

        if (is_string($client) && ! class_exists($client)) {
            $client = config("laravel-remote-models.clients.$client.client");
        }

        return $client;
    }

    public function getRemoteClient()
    {
        return resolve($this->getRemoteClientClass());
    }

    public function newRemoteQuery()
    {
        return (new Builder)->setModel($this);
    }

    public function newRemoteCollection(array $models = [])
    {
        return new Collection($models);
    }

    public function __call($method, $parameters)
    {
        try {
            return $this->forwardCallTo($this->newRemoteQuery(), $method, $parameters);
        } catch (BadMethodCallException $e) {
            return $this->forwardCallTo($this->newQuery(), $method, $parameters);
        }
    }
}
