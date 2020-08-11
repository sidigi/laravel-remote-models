<?php

namespace Sidigi\LaravelRemoteModels\Tests;

use GuzzleHttp\Client;
use Orchestra\Testbench\TestCase;
use Sidigi\LaravelRemoteModels\Contracts\Response as ResponseInterface;
use Sidigi\LaravelRemoteModels\JsonApiClient;
use Sidigi\LaravelRemoteModels\Response;

class ExampleTest extends TestCase
{
    /** @test */
    public function true_is_true()
    {
        app()->bind(ResponseInterface::class, Response::class);

        $client = app()->make(Client::class, [
            [
                'base_uri' => 'https://jsonplaceholder.typicode.com',
            ],
        ]);

        $jsonApiClient = resolve(JsonApiClient::class, [
            'client' => $client,
        ]);

        $response = $jsonApiClient
            ->withPath('index_comments', ['id' => 1])
            ->get()
            ->toArray();

        dd($response);
    }
}
