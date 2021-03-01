<?php

namespace Sidigi\LaravelRemoteModels\Mixins;

use Exception;
use Sidigi\LaravelRemoteModels\Pagination\PaginationStrategyInterface;
use Sidigi\LaravelRemoteModels\Response;
use Sidigi\LaravelRemoteModels\Services\JsonApiRequest\FilterService;
use Sidigi\LaravelRemoteModels\Services\JsonApiRequest\IncludeService;
use Sidigi\LaravelRemoteModels\Services\JsonApiRequest\PaginateService;
use Sidigi\LaravelRemoteModels\Services\JsonApiRequest\SortService;

class JsonApiRequestMixin
{
    public function filter()
    {
        return function ($column, $value = null) {
            return tap($this, function () use ($column, $value) {
                $this->options['query']['filter'] = (new FilterService($column, $value))->toJsonApi();
            });
        };
    }

    public function withFilter()
    {
        return function ($column, $value = null) {
            return tap($this, function () use ($column, $value) {
                $this->options['query']['filter'] = (new FilterService($column, $value))
                    ->withFilter($this->options['query']['filter'] ?? [])
                    ->toJsonApi();
            });
        };
    }

    public function orderBy()
    {
        return function ($order, string $asc = 'asc') {
            return tap($this, function () use ($order, $asc) {
                $this->options['query']['sort'] = (new SortService($order, $asc))
                    ->withSort($this->options['query']['sort'] ?? '')
                    ->toJsonApi();
            });
        };
    }

    public function orderByDesc()
    {
        return function ($order) {
            return tap($this, function () use ($order) {
                $this->options['query']['sort'] = (new SortService($order, 'desc'))
                ->withSort($this->options['query']['sort'] ?? '')
                ->toJsonApi();
            });
        };
    }

    public function include()
    {
        return function (...$includes) {
            return tap($this, function () use ($includes) {
                $this->options['query']['include'] = (new IncludeService($includes))->toJsonApi();
            });
        };
    }

    public function paginate()
    {
        return function ($data) {
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

    public function setPaginationStrategy()
    {
        return function (string $strategyClass, string $responseKey, array $defaults) {
            return tap($this, function () use ($strategyClass, $responseKey, $defaults) {
                $strategy = resolve($strategyClass, ['request' => $this, 'responseKeyPageNumber' => $responseKey, 'defaults' => $defaults]);

                if (! $strategy instanceof PaginationStrategyInterface) {
                    throw new Exception(sprintf('%s must be instance of %s', get_class($strategy), PaginationStrategyInterface::class));
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

    public function perPage()
    {
        return function ($method = 'get', $sleep = null, ...$arguments) {
            /**
             * @var PaginationStrategyInterface
             */
            $strategy = $this->getPaginationStrategy();

            do {
                $response = $this->$method($this->options['path'] ?? '', $arguments);

                yield new Response($response, config('laravel-remote-models.defaults.response_key', 'data'));

                $strategy->prepareForNextRequest();

                if ($sleep) {
                    sleep($sleep);
                }
            } while (! $strategy->isFinalPage($response));
        };
    }
}
