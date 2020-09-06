<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Collection;
use Sidigi\LaravelRemoteModels\Exceptions\ClientNotFoundException;

abstract class Model extends EloquentModel
{
    protected function getClientClass() : string
    {
        if (! $client = config('laravel-remote-models.models.'.static::class)) {
            throw new ClientNotFoundException('Client not found');
        }

        if (is_string($client) && ! class_exists($client)) {
            $client = config("laravel-remote-models.clients.$client.client");
        }

        return $client;
    }

    public function getClient() : ClientInterface
    {
        return resolve($this->getClientClass());
    }

    public static function query()
    {
        return (new static)->newQuery();
    }

    public function newQuery()
    {
        return (new Builder)->setModel($this);
    }

    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }
}
