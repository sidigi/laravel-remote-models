<?php

namespace Sidigi\LaravelRemoteModels\Tests;

use Orchestra\Testbench\TestCase;
use Sidigi\LaravelRemoteModels\JsonApiClient;

class ExampleTest extends TestCase
{
    /** @test */
    public function true_is_true()
    {
        $jsonApiClient = resolve(JsonApiClient::class, [
            'options' => [
                'base_uri' => 'https://jsonplaceholder.typicode.com',
            ],
        ]);

        $response = $jsonApiClient
            ->withHeaders([
                'X-First' => 'foo',
                'X-Second' => 'bar',
            ])
            ->withPath('/asdf/asdf')
            ->filter(['ff' => 1])
            ->get()
            ->json();
    }
}
