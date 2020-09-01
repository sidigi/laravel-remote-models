<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use Sidigi\LaravelRemoteModels\JsonApi\Pagination\Contracts\PaginationStrategyContract;

class Client implements ClientInterface
{
    use ForwardsCalls;

    private $passthru = ['get', 'head', 'post', 'put', 'patch', 'delete'];

    protected PendingRequest $client;
    protected UrlManager $urlManager;
    protected PaginationStrategyContract $paginationStrategy;
    protected ?string $path;
    protected array $query = [];
    protected string $responseKey;

    public function __construct(
        PendingRequest $client,
        UrlManager $urlManager,
        PaginationStrategyContract $paginationStrategy,
        string $path = null,
        string $responseKey = ''
    ) {
        $this->client = $client;
        $this->urlManager = $urlManager;
        $this->paginationStrategy = $paginationStrategy;
        $this->path = $path;
        $this->responseKey = $responseKey;
    }

    public function getResponseKey() : string
    {
        return $this->responseKey;
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
            preg_replace('/([^:])(\/{2,})/', '$1/', $url),
            $parameters
        );
    }

    public function __call($method, $arguments)
    {
        if (in_array($method, $this->passthru)) {
            return $this->passthru($method, ...$arguments);
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

    protected function passthru($method, ...$arguments)
    {
        if (! isset($arguments[0]) || (isset($arguments[0]) && ! is_string($arguments[0]))) {
            array_unshift($arguments, '');
        }

        $url = preg_replace('/(\/+)/', '/', $this->path.'/'.$arguments[0]);
        $query = $query[1] ?? [];
        $parameters = $arguments[2] ?? [];

        $url = $this->getUrl(
            $url,
            $query
        );

        $response = $this->forwardCallTo(
            $this->client,
            $method,
            [
                $url,
                (in_array($method, ['get', 'head']))
                    ? $query + $this->getQuery()
                    : $parameters,
            ]
        );

        return new Response($response, $this->getResponseKey());
    }
}
