<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Database\Eloquent\Model;
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

        return $instance->newRemoteCollection(array_map(function ($item) use ($instance) {
            return $instance->newFromBuilder($item);
        }, $items));
    }

    public function setModel(Model $model)
    {
        $this->model = $model;
        $this->client = $model->getRemoteClient();

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
}
