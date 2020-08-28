<?php

namespace Sidigi\LaravelRemoteModels;

use Closure;
use Illuminate\Http\Client\Response as HttpClientResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;

class Response
{
    use ForwardsCalls;

    protected $models;
    protected HttpClientResponse $response;

    public function __construct(HttpClientResponse $response)
    {
        $this->response = $response;
        $this->models = new Collection();
    }

    public function setModels(array $items, Model $model, ?Closure $callback)
    {
        $items = $this->isArrayOfItems($items) ? $items : [$items];

        if (is_callable($callback)) {
            $items = collect($items)->map(function ($item) use ($callback) {
                return ($callback)($item);
            })->toArray();
        }

        $this->models = $model->newCollection(
            $model->hydrate($items)->all()
        );
    }

    public function getModels()
    {
        return $this->models;
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
