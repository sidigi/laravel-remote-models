<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use InvalidArgumentException;
use Sidigi\LaravelRemoteModels\Contracts\JsonApiClientInterface;
use Sidigi\LaravelRemoteModels\Traits\HasJsonApiQueryFields;

class JsonApiClient implements JsonApiClientInterface
{
    use ForwardsCalls,
        HasJsonApiQueryFields;

    protected PendingRequest $client;
    protected UrlManager $urlManager;
    protected ?string $path;
    protected array $options;
    protected array $passthru = ['withHeaders'];

    public function __construct(PendingRequest $client, UrlManager $urlManager, array $options = [])
    {
        $this->client = $client;
        $this->urlManager = $urlManager;
        $this->path = null;
        $this->options = $options;
    }

    public function getPaths() : array
    {
        // get from config
        return [];
    }

    public function withPath(string $path, array $parameters = [])
    {
        $this->path = $this->getPaths()[$path] ?? $path;

        return $this;
    }

    public function get(string $url = null, array $parameters = [])
    {
        $url = $this->getFullUrl($url, Arr::except($parameters, ['query']));

        if (! $url) {
            throw new InvalidArgumentException('The given uri is null');
        }

        return $this->client->get(
            $url,
            $this->getQuery() + Arr::get($parameters, 'query', []) ?: []
        );
    }

    protected function getFullUrl(string $url = null, array $parameters = [])
    {
        if ($url && isset($this->getPaths()[$url])) {
            $url = $this->getPaths()[$url];
        }

        $baseUri = $this->options['base_uri'] ?? '';

        return $baseUri.'/'.$this->urlManager->resolve(
            $url ?: $this->path ?: '',
            $parameters
        );
    }

    public function __call($method, $arguments)
    {
        if ($path = $this->getPaths()[Str::snake($method)] ?? null) {
            $this->path = $path;
            $this->query = $this->query + $arguments;

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
