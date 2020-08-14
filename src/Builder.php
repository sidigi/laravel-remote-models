<?php

namespace Sidigi\LaravelRemoteModels;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\ForwardsCalls;

class Builder
{
    use ForwardsCalls;

    private $filterResponseItemCallback;

    protected Model $model;

    public function newModelInstance($attributes = [])
    {
        return $this->model->newInstance($attributes);
    }

    public function filterResponseItem(Closure $callback)
    {
        $this->filterResponseItemCallback = $callback;

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

        return $this;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function __call($method, $parameters)
    {
        $result = $this->forwardCallTo($this->client, $method, $parameters);

        if ($method === 'get') {
            $items = $result->json() ?? [];

            if ($key = $this->client->getResponseKey()) {
                $items = Arr::get($items, $key, []);
            }

            if (! $this->isArrayOfItems($items)) {
                if (is_callable($this->filterResponseItemCallback)) {
                    $items = ($this->filterResponseItemCallback)($items);
                }

                return $this->newModelInstance($items);
            }

            if (is_callable($this->filterResponseItemCallback)) {
                $items = collect($items)->map(function ($item) {
                    return ($this->filterResponseItemCallback)($item);
                })->toArray();
            }

            return $this->getModel()->newCollection(
                $this->model->hydrate($items)->all()
            );
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
