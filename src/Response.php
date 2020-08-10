<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\ForwardsCalls;

class Response
{
    use ForwardsCalls;

    private $response;

    public function __construct($response)
    {
        $this->response = $response;
    }

    public function response()
    {
        return $this->response;
    }

    public function json()
    {
        return json_decode($this->response->getBody()->getContents(), true);
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

    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->response, $method, $parameters);
    }
}
