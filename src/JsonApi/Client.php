<?php

namespace Sidigi\LaravelRemoteModels\JsonApi;

use Sidigi\LaravelRemoteModels\Client as BaseClient;
use Sidigi\LaravelRemoteModels\JsonApi\Traits\HasJsonApiQueryFields;

class Client extends BaseClient implements ClientInterface
{
    use HasJsonApiQueryFields;
}
