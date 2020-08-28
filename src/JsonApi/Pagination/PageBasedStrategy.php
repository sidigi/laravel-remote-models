<?php

namespace Sidigi\LaravelRemoteModels\JsonApi\Pagination;

use Exception;
use Illuminate\Support\Arr;
use Sidigi\LaravelRemoteModels\JsonApi\Client;
use Sidigi\LaravelRemoteModels\JsonApi\Pagination\Contracts\PaginationStrategyContract;
use Sidigi\LaravelRemoteModels\Response;

class PageBasedStrategy implements PaginationStrategyContract
{
    protected int $defaultNumber;
    protected int $defaultSize;
    protected string $responseNumberKey;

    public function __construct(array $defaults)
    {
        $this->defaultNumber = $defaults['number'];
        $this->defaultSize = $defaults['size'];
        $this->responseNumberKey = $defaults['response_number_key'];
    }

    public function set(Client $client, ...$arguments) : Client
    {
        return $client->withQuery([
            'page' => [
                'number' => $arguments[0] ?? $this->defaultNumber,
                'size' => $arguments[1] ?? $this->defaultSize,
            ],
        ]);
    }

    protected function getPagination(Client $client) : ?array
    {
        return Arr::get($client->getQuery(), 'page');
    }

    public function setNextPage(Response $response, Client $client) : void
    {
        if (! Arr::get($response->json(), $this->responseNumberKey)) {
            throw new Exception('Response has invalid meta.page_count key');
        }

        $pagination = $this->getPagination($client);

        $number = Arr::get($pagination, 'page.number', $this->defaultNumber);
        $size = Arr::get($pagination, 'page.size', $this->defaultSize);

        $this->set($client, $number++, $size);
    }

    public function isFinalPage(Response $response, Client $client) : bool
    {
        if (! $pageCount = Arr::get($response->json(), $this->responseNumberKey)) {
            return true;
        }

        if (! $number = Arr::get($this->getPagination($client), 'page.number')) {
            return true;
        }

        if ($pageCount > $number) {
            return false;
        }

        return true;
    }
}
