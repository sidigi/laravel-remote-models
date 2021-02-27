<?php

namespace Sidigi\LaravelRemoteModels\Clients\AwsLambda;

class Client
{
    public function __construct()
    {
        $this->awsClient = new \Aws\Lambda\LambdaClient([
            'version' => 'latest',
            'region' => env('AWS_REGION'),
        ]);
    }

    public function request(string $method, $uri = '', array $options = [])
    {
        return  $this->awsClient->invoke(
            array_merge(
                $options['lambda'] ?? [],
                [
                    'Payload' => json_encode(
                        $this->getPayload($method, $uri, $options)
                    ),
                ]
            )
        );
    }

    protected function getPayload(string $method, string $url, array $options = []) : array
    {
        return  [
            'path' => $url,
            'httpMethod' => $method,
            'headers' => $options['headers'] ?? [],
            'multiValueHeaders' => $options['multiValueHeaders'] ?? [],
            'queryStringParameters' => $options['query'] ?? [],
            'multiValueQueryStringParameters' => $options['multiValueQueryStringParameters'] ?? [],
            'pathParameters' => $options['pathParameters'] ?? [],
            'stageVariables' => $options['stageVariables'] ?? [],
            'requestContext' => $options['requestContext'] ?? [],
            'body' => $options['json'] ?? '',
            'isBase64Encoded' => $options['isBase64Encoded'] ?? false,
        ];
    }
}
