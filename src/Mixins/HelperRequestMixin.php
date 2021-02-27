<?php

namespace Sidigi\LaravelRemoteModels\Mixins;

use Sidigi\LaravelRemoteModels\Support\UrlManager;

class HelperRequestMixin
{
    public function withQuery()
    {
        return function (array $query) {
            return tap($this, function () use ($query) {
                $this->options['query'] = array_replace_recursive($this->options['query'] ?? [], $query);
            });
        };
    }

    public function getQuery()
    {
        return function () {
            return $this->options['query'] ?? [];
        };
    }

    public function withPath()
    {
        return function (string $path, array $parameters = []) {
            return tap($this, function () use ($path, $parameters) {
                $this->options['path'] = resolve(UrlManager::class)->resolve($path, $parameters);
            });
        };
    }

    public function getPath()
    {
        return function () {
            return $this->options['path'] ?? '';
        };
    }
}
