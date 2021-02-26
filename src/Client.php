<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;

class Client
{
    use ForwardsCalls;

    private $passthru = ['get', 'head', 'post', 'put', 'patch', 'delete'];

    protected PendingRequest $client;
    protected string $responseKey;

    public function __construct(
        PendingRequest $client,
        string $responseKey = 'data'
    ) {
        $this->client = $client;
        $this->responseKey = $responseKey;
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

    public function __call($method, $arguments)
    {
        if (in_array($method, $this->passthru)) {
            return $this->passthru($method, ...$arguments);
        }

        if ($path = $this->getPaths()[Str::snake($method)] ?? null) {
            $this->client->withPath($path, $arguments[0]);

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
        $url = count($arguments) <= 2 ? $arguments[0] ?? '' : '';
        $parameters = count($arguments) === 2 ? $arguments[1] : [];

        $url = ltrim(
            rtrim($this->getPath(), '/').'/'.ltrim($url, '/'),
            '/'
        );

        $response = $this->forwardCallTo(
            $this->client,
            $method,
            [
                $url,
                $parameters,
            ]
        );

        return new Response($response, $this->getResponseKey());
    }

    public function getResponseKey() : string
    {
        return $this->responseKey;
    }
}
