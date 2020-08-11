<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Support\Str;
use RuntimeException;

class UrlManager
{
    private UrlGenerator $urlGenerator;
    private Factory $validator;

    public function __construct(UrlGenerator $urlGenerator, Factory $validator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->validator = $validator;
    }

    public function resolve(string $path, array $parameters) : ?string
    {
        foreach ($parameters as $key => $parameter) {
            $path = Str::replaceFirst(sprintf('{%s}', $key), $parameter, $path);
        }

        $validator = $this->validator->make(['url' => $this->urlGenerator->to($path)], [
            'url' => 'required|url',
        ], [
            'url.url' => sprintf('The given url [%s] format is invalid.', $path),
        ]);

        if ($validator->fails()) {
            throw new RuntimeException($validator->errors()->first('url'));
        }

        return $path;
    }
}
