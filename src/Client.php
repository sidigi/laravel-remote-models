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
    protected array $paths = [];

    public function __construct(
        PendingRequest $client,
        string $responseKey = 'data',
        array $paths = []
    ) {
        $this->client = $client;
        $this->responseKey = $responseKey;
        $this->paths = $paths;
    }

    public function getPaths() : array
    {
        return $this->paths;
    }

    public function __call($method, $arguments)
    {
        if (in_array($method, $this->passthru)) {
            return $this->passthru($method, ...$arguments);
        }

        if ($path = $this->getPaths()[Str::snake($method)] ?? null) {
            $this->client->withPath($path, $arguments[0] ?? []);

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
