<?php

namespace Sidigi\LaravelRemoteModels\Providers;

use Sidigi\LaravelRemoteModels\Clients\AwsLambda\PendingRequest;

class AwsLambdaProvider implements ProviderInterface
{
    public function request() : PendingRequest
    {
        return resolve(PendingRequest::class);
    }
}
