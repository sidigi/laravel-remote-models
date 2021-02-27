<?php

namespace Sidigi\LaravelRemoteModels\Services\JsonApiRequest;

use Illuminate\Support\Arr;

class IncludeService
{
    protected array $includes = [];

    public function __construct(...$includes)
    {
        $this->includes = Arr::flatten($includes);

        return $this;
    }

    public function toJsonApi() : string
    {
        return implode(',', $this->includes);
    }
}
