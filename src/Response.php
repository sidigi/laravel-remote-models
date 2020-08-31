<?php

namespace Sidigi\LaravelRemoteModels;

use Closure;
use Illuminate\Http\Client\Response as HttpClientResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\ForwardsCalls;

class Response
{
    use ForwardsCalls;

    protected HttpClientResponse $response;
    protected string $responseKey;

    public function __construct(HttpClientResponse $response, string $responseKey)
    {
        $this->response = $response;
        $this->responseKey = $responseKey;
    }

    public function mapModel(string $model, Closure $callback = null, string $responseKey = null)
    {
        $responseKey = ! is_null($responseKey) ? $responseKey : $this->responseKey;

        $items = $this->json() ?? [];

        if ($responseKey) {
            $items = Arr::get($items, $responseKey, []);
        }

        return (new DataModelConverter($model))->convert($items, $callback);
    }

    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->response, $method, $parameters);
    }
}
