<?php

namespace Sidigi\LaravelRemoteModels;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\ForwardsCalls;

class Builder
{
    use ForwardsCalls;

    protected $responseKey = '';

    private $castItemCallback;

    protected Model $model;

    public function newModelInstance($attributes = [])
    {
        return $this->model->newInstance($attributes);
    }

    public function withResponseKey(?string $responseKey)
    {
        $this->responseKey = $responseKey;

        return $this;
    }

    public function castItem(Closure $callback)
    {
        $this->castItemCallback = $callback;

        return $this;
    }

    public function hydrate(array $items)
    {
        $instance = $this->newModelInstance();

        return $instance->newCollection(array_map(function ($item) use ($instance) {
            return $instance->newFromBuilder($item);
        }, $items));
    }

    public function setModel(Model $model)
    {
        $this->model = $model;
        $this->client = $model->getClient();
        $this->responseKey = config('laravel-remote-models.defaults.response_key');

        return $this;
    }

    public function getModel() : Model
    {
        return $this->model;
    }

    public function __call($method, $parameters)
    {
        $result = $this->forwardCallTo($this->client, $method, $parameters);

        if ($method === 'get') {
            $result = new Response($result);

            $items = $result->json() ?? [];

            if ($this->responseKey) {
                $items = Arr::get($items, $this->responseKey, []);
            }

            $result->setModels($items, $this->getModel(), $this->castItemCallback);

            return $result;
        }

        if (in_array($method, ['post', 'put', 'patch', 'delete'])) {
            return new Response($result);
        }

        return $this;
    }

    private function isArrayOfItems(array $items) : bool
    {
        $itemsOfItemsCount = collect($items)->filter(function ($item) {
            return is_array($item);
        })->count();

        return count($items) === $itemsOfItemsCount;
    }
}
