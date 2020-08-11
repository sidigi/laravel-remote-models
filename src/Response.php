<?php

namespace Sidigi\LaravelRemoteModels;

use GuzzleHttp\Psr7\MessageTrait;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\ForwardsCalls;
use Psr\Http\Message\ResponseInterface;

class Response implements ResponseInterface, Jsonable, Arrayable
{
    use ForwardsCalls, MessageTrait;

    private ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function toJson($options = 0) : string
    {
        return $this->response->getBody()->getContents();
    }

    public function toArray()
    {
        return json_decode($this->toJson(), true);
    }

    public function hasStatus(...$statuses)
    {
        $statuses = Arr::flatten($statuses);

        return in_array($this->response->getStatusCode(), $statuses);
    }

    public function isOk()
    {
        return $this->hasStatus(HttpResponse::HTTP_OK);
    }

    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        return $this->response->getStatusCode($code, $reasonPhrase);
    }

    public function getReasonPhrase()
    {
        return $this->response->getReasonPhrase();
    }

    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->response, $method, $parameters);
    }
}
