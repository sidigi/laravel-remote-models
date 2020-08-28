<?php

namespace Sidigi\LaravelRemoteModels\JsonApi\Pagination;

use Exception;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Sidigi\LaravelRemoteModels\JsonApi\Client;
use Sidigi\LaravelRemoteModels\JsonApi\Pagination\Contracts\PaginationStrategyContract;
use Sidigi\LaravelRemoteModels\Response;

class PageBasedStrategy implements PaginationStrategyContract
{
    protected int $defaultNumber;
    protected int $defaultSize;

    public function __construct(array $defaults)
    {
        $this->defaultNumber = $defaults['number'];
        $this->defaultSize = $defaults['size'];
    }

    public function set(Client $client, array $data) : Client
    {
        if (! Arr::has($data, ['size', 'number'])) {
            throw new InvalidArgumentException('keys size and number are required');
        }

        return $client->withQuery([
            'page' => [
                'number' => $data['number'] ?? $this->defaultNumber,
                'size' => $data['size'] ?? $this->defaultSize,
            ],
        ]);
    }

    public function get(Client $client) : ?array
    {
        return Arr::get($client->getQuery(), 'page');
    }

    public function setNextPage(Response $response, Client $client)
    {
        if (! Arr::get($response->json(), 'meta.page_count')) {
            throw new Exception('Response has invalid meta.page_count key');
        }

        $clientPage = Arr::get($this->get($client), 'page.number');
        $clientSize = Arr::get($this->get($client), 'page.size');

        return $this->set($client, [
            'number' => $clientPage++,
            'size' => $clientSize,
        ]);
    }

    public function isFinalPage(Response $response, Client $client)
    {
        if (! $pageCount = Arr::get($response->json(), 'meta.page_count')) {
            return true;
        }

        if (! $clientPage = Arr::get($this->get($client), 'page.number')) {
            return true;
        }

        if ($pageCount > $clientPage) {
            return false;
        }
    }
}
