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
    protected ?string $key;

    public function __construct(HttpClientResponse $response, ?string $key = null)
    {
        $this->response = $response;
        $this->key = ! is_null($key)
            ? $key
            : config('laravel-remote-models.defaults.response_key', '');
    }

    public function withKey(string $key)
    {
        $this->key = $key;

        return $this;
    }

    public function mapModel(string $model, Closure $callback = null, ?string $responseKey = null)
    {
        $model = new $model;
        $responseKey = ! is_null($responseKey) ? $responseKey : $this->key;

        $items = $this->json() ?? [];

        if ($responseKey) {
            $items = Arr::get($items, $responseKey, []);
        }

        $items = $this->isArrayOfItems($items) ? $items : [$items];

        if (is_callable($callback)) {
            $items = collect($items)->map(function ($item) use ($callback) {
                return ($callback)($item);
            })->toArray();
        }

        return $model->newCollection(
            $model->hydrate($items)->all()
        );
    }

    private function isArrayOfItems(array $items) : bool
    {
        $itemsOfItemsCount = collect($items)->filter(fn ($item) => is_array($item))->count();

        return count($items) === $itemsOfItemsCount;
    }

    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->response, $method, $parameters);
    }
}
