<?php

namespace Sidigi\LaravelRemoteModels\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Sidigi\LaravelRemoteModels\Clients\AwsLambda\PendingRequest;

/**
 * @method static PendingRequest functionName(string $name)
 * @method static PendingRequest withLambda(array $options)
 * @see \Illuminate\Support\Facades\Http
 */
class AwsLambda extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'aws-lambda';
    }
}
