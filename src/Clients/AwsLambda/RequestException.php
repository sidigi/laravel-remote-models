<?php

namespace Sidigi\LaravelRemoteModels\Clients\AwsLambda;

use Illuminate\Http\Client\HttpClientException;

class RequestException extends HttpClientException
{
    public $response;

    public function __construct(Response $response)
    {
        parent::__construct($this->prepareMessage($response), $response->status());

        $this->response = $response;
    }

    /**
     * Prepare the exception message.
     *
     * @param  \Illuminate\Http\Client\Response  $response
     * @return string
     */
    protected function prepareMessage(Response $response)
    {
        $message = "HTTP request returned status code {$response->status()}";

        $summary = $this->getSummary($response);

        return is_null($summary) ? $message : $message .= ":\n{$summary}\n";
    }

    protected function getSummary(Response $response, $truncateAt = 120)
    {
        $body = $response->getBody();

        if (! $body->isSeekable() || ! $body->isReadable()) {
            return;
        }

        $size = $body->getSize();

        if ($size === 0) {
            return;
        }

        $summary = $body->read($truncateAt);

        $body->rewind();

        if ($size > $truncateAt) {
            $summary .= ' (truncated...)';
        }

        // Matches any printable character, including unicode characters:
        // letters, marks, numbers, punctuation, spacing, and separators.
        if (preg_match('/[^\pL\pM\pN\pP\pS\pZ\n\r\t]/u', $summary)) {
            return;
        }

        return $summary;
    }
}
