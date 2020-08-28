<?php

namespace Sidigi\LaravelRemoteModels\JsonApi;

use Sidigi\LaravelRemoteModels\Client as BaseClient;
use Sidigi\LaravelRemoteModels\JsonApi\Traits\HasJsonApiQueryFields;

class Client extends BaseClient implements ClientInterface
{
    use HasJsonApiQueryFields;

    public function pageIterator($sleep = null, ...$arguments)
    {
        do {
            $response = $this->get(...$arguments);

            yield $response;

            $this->paginationStrategy->setNextPage($response, $this);

            if ($sleep) {
                sleep($sleep);
            }
        } while (! $this->paginationStrategy->isFinalPage($response, $this));
    }
}
