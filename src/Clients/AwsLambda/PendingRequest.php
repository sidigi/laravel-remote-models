<?php

namespace Sidigi\LaravelRemoteModels\Clients\AwsLambda;

use Aws\Connect\Exception\ConnectException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest as ClientPendingRequest;

class PendingRequest extends ClientPendingRequest
{
    public function __construct(Factory $factory = null)
    {
        parent::__construct($factory);

        $this->withLambda([
            'InvocationType' => 'RequestResponse',
            'LogType' => 'None',
        ]);
    }

    public function functionName(string $name) : self
    {
        return $this->withLambda(['FunctionName' => $name]);
    }

    public function withLambda(array $options) : self
    {
        return tap($this, function ($request) use ($options) {
            return $this->options = array_merge_recursive($this->options, [
                'lambda' => $options,
            ]);
        });
    }

    public function send(string $method, string $url, array $options = [])
    {
        $url = ltrim(rtrim($this->baseUrl, '/').'/'.ltrim($url, '/'), '/');

        if (isset($options[$this->bodyFormat])) {
            if ($this->bodyFormat === 'multipart') {
                $options[$this->bodyFormat] = $this->parseMultipartBodyFormat($options[$this->bodyFormat]);
            } elseif ($this->bodyFormat === 'body') {
                $options[$this->bodyFormat] = $this->pendingBody;
            }

            if (is_array($options[$this->bodyFormat])) {
                $options[$this->bodyFormat] = array_merge(
                    $options[$this->bodyFormat],
                    $this->pendingFiles
                );
            }
        }

        [$this->pendingBody, $this->pendingFiles] = [null, []];

        return retry($this->tries ?? 1, function () use ($method, $url, $options) {
            try {
                $laravelData = $this->parseRequestData($method, $url, $options);

                return tap(new Response($this->buildClient()->request($method, $url, $this->mergeOptions([
                    'laravel_data' => $laravelData,
                    'on_stats' => function ($transferStats) {
                        $this->transferStats = $transferStats;
                    },
                ], $options))), function ($response) {
                    $response->cookies = $this->cookies;
                    $response->transferStats = $this->transferStats;

                    if ($this->tries > 1 && ! $response->successful()) {
                        $response->throw();
                    }
                });
            } catch (ConnectException $e) {
                throw new ConnectionException($e->getMessage(), 0, $e);
            }
        }, $this->retryDelay ?? 100);
    }

    public function buildClient()
    {
        return new Client;
    }
}
