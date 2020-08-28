<?php

namespace Sidigi\LaravelRemoteModels\JsonApi\Pagination\Contracts;

use Sidigi\LaravelRemoteModels\JsonApi\Client;
use Sidigi\LaravelRemoteModels\Response;

interface PaginationStrategyContract
{
    public function set(Client $client, array $data) : Client;

    public function setNextPage(Response $response, Client $client);

    public function isFinalPage(Response $response, Client $client);
}
