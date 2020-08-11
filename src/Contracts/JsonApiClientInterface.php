<?php

namespace Sidigi\LaravelRemoteModels\Contracts;

interface JsonApiClientInterface
{
    public function filter($column, $value = null) : self;

    public function include(...$includes) : self;

    public function orderBy(string $order, bool $asc = true) : self;

    public function orderByDesc(string $order) : self;

    public function paginate(int $size = null, int $number = 1) : self;

    public function getQuery() : array;
}
