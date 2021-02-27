<?php

namespace Sidigi\LaravelRemoteModels\Services\JsonApiRequest;

use Illuminate\Support\Str;

class SortService
{
    protected array $sorts = [];

    public function __construct($order, string $asc = 'asc')
    {
        if (! is_array($order)) {
            $order = [$order => $asc];
        }

        collect($order)->each(function ($item, $key) {
            $this->sorts[$key] = Str::lower($item) === 'asc' ? true : false;
        });

        return $this;
    }

    public function toJsonApi() : string
    {
        return collect($this->sorts)->map(function ($item, $key) {
            return $item ? $key : '-'.$key;
        })
            ->values()
            ->implode(',');
    }

    public function withSort(string $sort)
    {
        return tap($this, function () use ($sort) {
            $sorts = collect(explode(',', $sort))
                ->filter()
                ->mapWithKeys(function ($value) {
                    $key = $value;

                    if ($isDesc = Str::startsWith('-', $value)) {
                        $key = Str::replaceFirst('-', '', $value);
                    }

                    return [$key => $isDesc ? 'desc' : 'asc'];
                })
                ->toArray();

            return $this->sorts = array_merge_recursive($this->sorts, $sorts);
        });
    }
}
