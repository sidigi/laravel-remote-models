<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;

class Client implements ClientInterface
{
    use ForwardsCalls;

    protected PendingRequest $client;
    protected UrlManager $urlManager;
    protected ?string $path;
    protected array $query = [];

    public function __construct(
        PendingRequest $client,
        UrlManager $urlManager,
        string $path = null
    ) {
        $this->client = $client;
        $this->urlManager = $urlManager;
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

    public function get(string $url = null, array $parameters = [])
    {
        $url = $this->getUrl(
            $url ?? $this->path,
            $parameters
        );

        return $this->client->get(
            $url,
            $this->getQuery()
        );
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
