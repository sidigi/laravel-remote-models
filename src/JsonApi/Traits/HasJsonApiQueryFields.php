<?php

namespace Sidigi\LaravelRemoteModels\JsonApi\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

trait HasJsonApiQueryFields
{
    protected array $filters = [];
    protected array $sorts = [];
    protected array $pagination = [];
    protected array $includes = [];

    public function include(...$includes) : self
    {
        $includes = Arr::flatten($includes);

        $this->includes = $this->sanitizeArray(array_merge($this->includes, $includes));

        return $this;
    }

    public function orderBy(string $order, bool $asc = true) : self
    {
        if (! is_array($order)) {
            $order = [$order => $asc];
        }

        collect($order)->each(function ($item, $key) {
            $this->sorts[] = [$item => Str::lower($item) === 'asc' ? true : false];
        });

        return $this;
    }

    public function orderByDesc(string $order) : self
    {
        $this->orderBy($order, false);

        return $this;
    }

    public function paginate(array $data) : self
    {
        return $this->paginationStrategy->set($this, $data);
    }

    public function getQuery() : array
    {
        $query = $this->query;

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

    public function filter($column, $value = null) : self
    {
        if (is_array($column)) {
            return $this->addArrayOfFilters($column);
        }

        $this->filters[$column] = $this->sanitizeArray([$value]);

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

    public function withQuery(array $query = [])
    {
        $this->query = $query + $this->query;

        return $this;
    }
}
