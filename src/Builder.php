<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Support\Arr;
use InvalidArgumentException;

class Builder
{
    protected Model $model;

    private array $filters = [];
    private array $sorts = [];
    private array $pagination = [];
    private array $includes = [];

    protected string $path = '';
    private array $queryParams = [];

    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    public function filter($column, $value = null) : self
    {
        if (is_array($column)) {
            return $this->addArrayOfFilters($column);
        }

        $this->filters[$column] = $this->sanitizeArray([$value]);

        return $this;
    }

    public function withQueryParams(array $queryParams) : self
    {
        $this->queryParams = $this->queryParams + $queryParams;

        return $this;
    }

    public function withPath(string $path) : self
    {
        $this->path = $path;

        return $this;
    }

    public function include(...$includes) : self
    {
        $includes = Arr::flatten($includes);

        $this->includes = $this->sanitizeArray(array_merge($this->includes, $includes));

        return $this;
    }

    public function orderBy(string $order, bool $asc = true) : self
    {
        $this->sorts[] = [$order => $asc];

        return $this;
    }

    public function orderByDesc(string $order) : self
    {
        $this->orderBy($order, false);

        return $this;
    }

    public function paginate(int $size = null, int $number = 1) : self
    {
        $this->pagination = [
            'size' => $size,
            'number' => $number,
        ];

        return $this;
    }

    protected function addArrayOfFilters($filters) : self
    {
        foreach ($filters as $key => $value) {
            if (! is_scalar($value) && ! is_array($value)) {
                throw new InvalidArgumentException('Filter column must be array or scalar');
            }

            $this->filters[$key] = $this->sanitizeArray(
                Arr::flatten([$value])
            );
        }

        return $this;
    }

    private function sanitizeArray(array $data = []) : array
    {
        return collect($data)
            ->flatten()
            ->unique()
            ->toArray();
    }

    private function getQueryParams() : array
    {
        $query = $this->queryParams;

        $query['filter'] = collect($this->filters)->mapWithKeys(function ($value, $key) {
            return [$key => implode(',', $value)];
        })->toArray();

        $query['sort'] = collect($this->sorts)->map(function ($item) {
            return current($item) ? key($item) : '-'.key($item);
        })->implode(',');

        $query['include'] = implode(',', $this->includes);

        $query['page'] = $this->pagination;

        return collect($query)->filter()->toArray();
    }

    public function getModel()
    {
        return $this->model;
    }
}
