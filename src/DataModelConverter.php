<?php

namespace Sidigi\LaravelRemoteModels;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class DataModelConverter
{
    private Model $model;

    public function __construct(string $model)
    {
        $model = new $model;

        if (! $model instanceof Model) {
            throw new InvalidArgumentException("Given class [$model] must be instance of ".Model::class);
        }

        $this->model = $model;
    }

    public function convert(array $items, Closure $callback = null) : Collection
    {
        $items = $this->isArrayOfItems($items) ? $items : [$items];

        if (is_callable($callback)) {
            $items = collect($items)->map(function ($item) use ($callback) {
                return ($callback)($item);
            })->toArray();
        }

        return $this->model->newCollection(
            $this->model->hydrate($items)->all()
        );
    }

    protected function isArrayOfItems(array $items) : bool
    {
        $itemsOfItemsCount = collect($items)->filter(fn ($item) => is_array($item))->count();

        return count($items) === $itemsOfItemsCount;
    }
}
