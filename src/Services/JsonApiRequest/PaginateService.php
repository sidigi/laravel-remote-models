<?php

namespace Sidigi\LaravelRemoteModels\Services\JsonApiRequest;

use Sidigi\LaravelRemoteModels\Pagination\PaginationStrategyInterface;

class PaginateService
{
    protected PaginationStrategyInterface $strategy;

    public function __construct(PaginationStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    public function toJsonApi() : array
    {
        return $this->strategy->toArray();
    }
}
