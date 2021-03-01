<?php

namespace Sidigi\LaravelRemoteModels\Clients\AwsLambda;

use Illuminate\Http\Client\Response as ClientResponse;

class Response extends ClientResponse
{
    protected $decodedBody;

    public function json($key = null, $default = null)
    {
        if (! $this->decodedBody) {
            $this->decodedBody = json_decode($this->body(), true);
        }

        if (is_null($key)) {
            return $this->decodedBody;
        }

        return data_get($this->decodedBody, $key, $default);
    }

    public function getPayload() : array
    {
        if (! $this->decoded) {
            $this->decoded = json_decode($this->getBody()->getContents(), true);
        }

        return $this->decoded;
    }

    public function getBody()
    {
        return $this->response->get('Payload');
    }

    public function body() : string
    {
        return $this->getPayload()['body'];
    }

    public function status() : int
    {
        return (int) $this->getPayload()['statusCode'];
    }

    public function getHeaders() : array
    {
        return $this->getPayload()['multiValueHeaders'];
    }

    public function getHeader(string $header) : array
    {
        $header = strtolower($header);

        $headers = $this->getHeaders();

        foreach ($headers as $key => $item) {
            if (strtolower($header) === strtolower($key)) {
                return $item;
            }
        }

        return [];
    }

    public function throw()
    {
        $callback = func_get_args()[0] ?? null;

        if ($this->failed()) {
            throw tap(new RequestException($this), function ($exception) use ($callback) {
                if ($callback && is_callable($callback)) {
                    $callback($this, $exception);
                }
            });
        }

        return $this;
    }
}
