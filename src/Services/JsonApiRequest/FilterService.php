<?php

namespace Sidigi\LaravelRemoteModels\Services\JsonApiRequest;

use Illuminate\Support\Arr;
use InvalidArgumentException;

class FilterService
{
    protected array $filters = [];

    public function __construct($column, $value = null)
    {
        if (is_array($column)) {
            return $this->addArrayOfFilters($column);
        }

        $this->filters[$column] = $this->sanitizeArray([$value]);

        return $this;
    }

    public function withFilter(array $filters)
    {
        return tap($this, function () use ($filters) {
            $filters = collect($filters)->mapWithKeys(function ($value, $key) {
                return [$key => explode(',', $value)];
            })->toArray();

            return $this->filters = array_merge_recursive($this->filters, $filters);
        });
    }

    public function toJsonApi() : array
    {
        return $this->filters = collect($this->filters)->mapWithKeys(function ($value, $key) {
            return [$key => implode(',', $value)];
        })->toArray();
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
}
