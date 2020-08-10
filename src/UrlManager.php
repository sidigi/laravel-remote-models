<?php

namespace App\JsonApi;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use RuntimeException;

class UrlManager
{
    public function bindParameters(string $path, array $parameters) : ?string
    {
        foreach ($parameters as $key => $parameter) {
            $path = Str::replaceFirst(sprintf('{%s}', $key), $parameter, $path);
        }

        return $this->validate($path);
    }

    public function resolve(string $method, array $paths) : ?string
    {
        return Arr::get($paths, $method) ?: Arr::get($paths, Str::snake($method));
    }

    private function validate(string $path)
    {
        $validator = Validator::make(['url' => url($path)], [
            'url' => 'required|url',
        ]);

        if ($validator->fails()) {
            throw new RuntimeException(sprintf($validator->errors()->first('url').' [%s]', $path));
        }

        return $path;
    }
}
