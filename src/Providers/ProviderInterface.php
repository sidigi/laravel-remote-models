<?php

namespace Sidigi\LaravelRemoteModels\Providers;

use Illuminate\Http\Client\PendingRequest;

interface ProviderInterface
{
    public function request() : PendingRequest;
}
