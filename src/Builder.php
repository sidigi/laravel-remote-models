<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Support\Traits\ForwardsCalls;

class Builder
{
    use ForwardsCalls;

    protected Model $model;

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

    public function getModel()
    {
        return $this->model;
    }

    public function __call($method, $parameters)
    {
        $result = $this->forwardCallTo($this->client, $method, $parameters);

        if ($method === 'get') {
            return $this->getModel()->newCollection(
                $this->model->hydrate($result->json() ?? [])->all()
            );
        }

        return $this;
    }
}
