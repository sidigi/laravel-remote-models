<?php

namespace Sidigi\LaravelRemoteModels\Mixins;

use Exception;
use Sidigi\LaravelRemoteModels\Pagination\PaginationStrategyInterface;
use Sidigi\LaravelRemoteModels\Services\JsonApiRequest\FilterService;
use Sidigi\LaravelRemoteModels\Services\JsonApiRequest\IncludeService;
use Sidigi\LaravelRemoteModels\Services\JsonApiRequest\PaginateService;
use Sidigi\LaravelRemoteModels\Services\JsonApiRequest\SortService;

class JsonApiRequestMixin
{
    public function filter($column, $value = null)
    {
        return function () use ($column, $value) {
            return tap($this, function () use ($column, $value) {
                $this->options['query']['filter'] = (new FilterService($column, $value))->toJsonApi();
            });
        };
    }

    public function withFilter($column, $value = null)
    {
        return function () use ($column, $value) {
            return tap($this, function () use ($column, $value) {
                $this->options['query']['filter'] = (new FilterService($column, $value))
                    ->withFilter($this->options['query']['filter'] ?? [])
                    ->toJsonApi();
            });
        };
    }

    public function orderBy($order, string $asc = 'asc')
    {
        return function () use ($order, $asc) {
            return tap($this, function () use ($order, $asc) {
                $this->options['query']['sort'] = (new SortService($order, $asc))
                    ->withSort($this->options['query']['sort'] ?? '')
                    ->toJsonApi();
            });
        };
    }

    public function orderByDesc($order)
    {
        return function () use ($order) {
            return tap($this, function () use ($order) {
                $this->options['query']['sort'] = (new SortService($order, 'desc'))
                ->withSort($this->options['query']['sort'] ?? '')
                ->toJsonApi();
            });
        };
    }

    public function include(...$includes)
    {
        return function () use ($includes) {
            return tap($this, function () use ($includes) {
                $this->options['query']['include'] = (new IncludeService($includes))->toJsonApi();
            });
        };
    }

    public function paginate($data)
    {
        return function () use ($data) {
            return tap($this, function () use ($data) {
                $strategy = $this->options['pagination_strategy'];

                if (isset($data['number'])) {
                    $strategy->number($data['number']);
                }

                if (isset($data['size'])) {
                    $strategy->size($data['size']);
                }

                $this->options['query']['page'] = (new PaginateService($strategy))
                    ->toJsonApi();
            });
        };
    }

    public function setPaginationStrategy(string $strategyClass, string $responseKey, array $defaults)
    {
        return function () use ($strategyClass, $responseKey, $defaults) {
            return tap($this, function () use ($strategyClass, $responseKey, $defaults) {
                $strategy = resolve($strategyClass, ['request' => $this, 'responseKeyPageNumber' => $responseKey, 'defaults' => $defaults]);

                if (! $strategy instanceof PaginationStrategyInterface) {
                    throw new Exception(sprintf('%s must be instance of %s', $strategy::class, PaginationStrategyInterface::class));
                }

                $this->options['pagination_strategy'] = $strategy;
            });
        };
    }

    public function getPaginationStrategy()
    {
        return function () {
            return $this->options['pagination_strategy'] ?? null;
        };
    }

    public function perPage($method = 'get', $sleep = null, ...$arguments)
    {
        return function () use ($method, $sleep, $arguments) {
            $strategy = $this->getPaginationStrategy();

            do {
                $response = $this->$method(...$arguments);

                yield $response;

                $strategy->prepareForNextRequest();

                if ($sleep) {
                    sleep($sleep);
                }
            } while (! $strategy->isFinalPage($response));
        };
    }
}
