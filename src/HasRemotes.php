<?php

namespace Sidigi\LaravelRemoteModels;

use Sidigi\LaravelRemoteModels\Exceptions\ClientNotFoundException;

trait HasRemotes
{
    protected static function getClientOptions() : array
    {
        if (! $client = config('laravel-remote-models.models.'.static::class)) {
            throw new ClientNotFoundException('Client not found');
        }

        if (is_string($client) && ! class_exists($client)) {
            return [$client, config("laravel-remote-models.clients.$client")];
        }

        if (is_array($client)) {
            return [$client];
        }
    }

    public static function getRemoteClient(string $client = null)
    {
        [$alias] = static::getClientOptions();

        if (is_array($alias)) {
            $alias = $client
                ? $alias[$client]
                : reset($alias);
        }

        return resolve($alias);
    }
}
