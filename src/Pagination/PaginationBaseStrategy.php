<?php

namespace Sidigi\LaravelRemoteModels\Pagination;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;

class PaginationBaseStrategy implements PaginationStrategyInterface
{
    protected PendingRequest $request;
    protected string $responseKeyPageNumber;
    protected array $defaults;

    public function __construct(PendingRequest $request, string $responseKeyPageNumber, array $defaults)
    {
        $this->request = $request;
        $this->responseKeyPageNumber = $responseKeyPageNumber;
        $this->defaults = $defaults;

        $this->number($this->defaults['number'])
            ->size($this->defaults['size']);
    }

    public function toArray() : array
    {
        return [
            'number' => $this->getNumber(),
            'size' => $this->getSize(),
        ];
    }

    public function number(int $number) : self
    {
        $this->request->withQuery(['page' => ['number' => $number]]);

        return $this;
    }

    public function size(int $size) : self
    {
        $this->request->withQuery(['page' => ['size' => $size]]);

        return $this;
    }

    public function getNumber() : int
    {
        return Arr::get($this->request->getQuery(), 'page.number', $this->defaults['number']);
    }

    public function getSize() : int
    {
        return Arr::get($this->request->getQuery(), 'page.size', $this->defaults['size']);
    }

    public function getResponseKeyPageNumber() : string
    {
        return $this->responseKeyPageNumber;
    }

    public function isFinalPage(Response $response) : bool
    {
        //empty answer
        if (! $response->json('data')) {
            return true;
        }

        $allPages = (int) $response->json($this->getResponseKeyPageNumber());

        if ($allPages >= $this->getNumber()) {
            return false;
        }

        return true;
    }

    public function prepareForNextRequest() : void
    {
        $this->number($this->getNumber() + 1);
    }
}
