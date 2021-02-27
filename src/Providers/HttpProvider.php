<?php

namespace Sidigi\LaravelRemoteModels\Providers;

use Illuminate\Http\Client\PendingRequest;

class HttpProvider implements ProviderInterface
{
    public function request() : PendingRequest
    {
        return resolve(PendingRequest::class);
    }
}
