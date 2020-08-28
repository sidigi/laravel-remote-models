<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use Sidigi\LaravelRemoteModels\JsonApi\Pagination\Contracts\PaginationStrategyContract;

class Client implements ClientInterface
{
    use ForwardsCalls;

    protected PendingRequest $client;
    protected UrlManager $urlManager;
    protected PaginationStrategyContract $paginationStrategy;
    protected ?string $path;
    protected array $query = [];

    public function __construct(
        PendingRequest $client,
        UrlManager $urlManager,
        PaginationStrategyContract $paginationStrategy,
        string $path = null
    ) {
        $this->client = $client;
        $this->urlManager = $urlManager;
        $this->paginationStrategy = $paginationStrategy;
        $this->path = $path;
    }

    public function getPaths() : array
    {
        return collect(config('laravel-remote-models.clients'))
            ->first(function ($client) {
                if ($this instanceof $client['client']) {
                    return true;
                }
            })['paths'] ?? [];
    }

    public function withPath(string $path, array $parameters = [])
    {
        $this->path = $this->getUrl($this->getPaths()[$path] ?? $path, $parameters);

        return $this;
    }

    public function withQuery(array $query = [])
    {
        $this->query = $query + $this->query;

        return $this;
    }

    public function getQuery() : array
    {
        return $this->query;
    }

    protected function getUrl(string $url, array $parameters = [])
    {
        return $this->urlManager->resolve(
            $url,
            $parameters
        );
    }

    public function __call($method, $arguments)
    {
        if (in_array($method, ['post', 'put', 'patch', 'delete', 'get', 'head'])) {
            $url = $this->getUrl(
                $url ?? $this->path,
                $arguments
            );

            $response = $this->forwardCallTo(
                $this->client,
                $method,
                [$url, (in_array($method, ['get', 'head'])) ? $this->getQuery() : $arguments[1] ?? []]
            );

            return new Response($response);
        }

        if ($path = $this->getPaths()[Str::snake($method)] ?? null) {
            $this->path = $this->getUrl($path, ...$arguments);

            return $this;
        }

        $result = $this->forwardCallTo($this->client, $method, $arguments);

        if ($result instanceof PendingRequest) {
            $this->client = $result;

            return $this;
        }

        return $result;
    }
}
