<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use InvalidArgumentException;

class Client implements ClientInterface
{
    use ForwardsCalls;

    protected PendingRequest $client;
    protected UrlManager $urlManager;
    protected ?string $path;
    protected ?string $baseUri;
    protected array $passthru = ['withHeaders'];
    protected array $query = [];
    protected ?string $responseKey;

    public function __construct(
        PendingRequest $client,
        UrlManager $urlManager,
        string $baseUri = null,
        string $path = null
    ) {
        $this->client = $client;
        $this->urlManager = $urlManager;
        $this->path = $path;
        $this->baseUri = $baseUri;
        $this->responseKey = null;
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

    public function withBaseUri(string $baseUri)
    {
        $this->baseUri = $baseUri;

        return $this;
    }

    public function withQuery(array $query = [])
    {
        $this->query = $query + $this->query;

        return $this;
    }

    public function fromResponseKey(?string $responseKey)
    {
        $this->responseKey = $responseKey;

        return $this;
    }

    public function get(string $url = null, array $parameters = [])
    {
        $url = $this->getUrl(
            $this->baseUri.'/'.($url ?? $this->path),
            Arr::except($parameters, ['query'])
        );

        $url = preg_replace('#(?<!:)/+#', '/', $url);

        if (! $url) {
            throw new InvalidArgumentException('The given uri is null');
        }

        return $this->client->get(
            $url,
            $this->getQuery() + Arr::get($parameters, 'query', []) ?: []
        );
    }

    protected function getUrl(string $url, array $parameters = [])
    {
        return $this->urlManager->resolve(
            $url,
            $parameters
        );
    }

    public function getResponseKey()
    {
        return $this->responseKey;
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
