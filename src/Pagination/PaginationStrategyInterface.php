<?php

namespace Sidigi\LaravelRemoteModels\Pagination;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

interface PaginationStrategyInterface
{
    public function __construct(PendingRequest $request, string $responseKeyPageNumber, array $defaults);

    public function toArray() : array;

    public function getResponseKeyPageNumber() : string;

    public function isFinalPage(Response $response) : bool;

    public function prepareForNextRequest() : void;
}
