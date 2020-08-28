<?php

namespace Sidigi\LaravelRemoteModels\JsonApi\Pagination\Contracts;

use Sidigi\LaravelRemoteModels\JsonApi\Client;
use Sidigi\LaravelRemoteModels\Response;

interface PaginationStrategyContract
{
    public function __construct(array $defaults);

    public function set(Client $client, ...$arguments) : Client;

    public function setNextPage(Response $response, Client $client) : void;

    public function isFinalPage(Response $response, Client $client) : bool;
}
