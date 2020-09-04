<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Support\Traits\ForwardsCalls;

class Builder
{
    use ForwardsCalls;

    private $passthru = ['get', 'head', 'post', 'put', 'patch', 'delete'];

    protected Model $model;
    protected $client;

    public function newModelInstance($attributes = [])
    {
        return $this->model->newInstance($attributes);
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

    public function getModel() : Model
    {
        return $this->model;
    }

    public function __call($method, $parameters)
    {
        $result = $this->forwardCallTo($this->client, $method, $parameters);

        if (in_array($method, $this->passthru)) {
            return $result;
        }
        if ($method === 'perPage') {
            return $this->forwardCallTo($this->client, $method, $parameters);
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
