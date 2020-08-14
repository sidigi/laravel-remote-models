# Use remote requests in laravel eloquent models way

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sidigi/laravel-remote-models.svg?style=flat-square)](https://packagist.org/packages/sidigi/laravel-remote-models)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/sidigi/laravel-remote-models/run-tests?label=tests)](https://github.com/sidigi/laravel-remote-models/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/sidigi/laravel-remote-models.svg?style=flat-square)](https://packagist.org/packages/sidigi/laravel-remote-models)

This is where your description should go. Try and limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require sidigi/laravel-remote-models
```

## Usage

### Clients

```php
use Sidigi\LaravelRemoteModels\Client;

class CommentClient extends Client
{
}

$comments = Comment::get('/comments/{id}', ['id' => 1])->get();
$comments = Comment::withPath('/comments')->get();
$comments = Comment::withPath('/comments/{id}', ['id' => 1])->get();
$comments = Comment::withQuery(['active' => true])->get();
```


```php
use Sidigi\LaravelRemoteModels\Client;

class CommentClient extends Client
{
    public function getPaths() : array
    {
        return [
            'index_comments' => 'comments',
            'detail_comment' => 'comment/{id}',
        ]
    }
}

$comments = CommentClient::withPath('index_comments')->get();
$comments = CommentClient::indexComments()->get();
$comments = CommentClient::withPath('detail_comment', ['id' => 1])->get();
$comments = CommentClient::detailComment(['id' => 1])->get();
$comments = CommentClient::detailComment(['id' => 1])->withQuery(['active' => true])->get();
```

```php
use Sidigi\LaravelRemoteModels\JsonApi\Client;

class CommentClient extends Client
{
}

$comments = CommentClient::withPath('/comments/{id}', ['id' => 1])
                ->withQuery(['active' => true])
                ->filter(['id' => [1, 2, 3])
                ->include('posts.user')
                ->orderBy('created_at')
                ->paginate($size = 3, $number = 2)
                ->get();
```

### Models

```php

use Sidigi\LaravelRemoteModels\JsonApi\Client;

class CommentClient extends Client
{
}

class Comment extends Model
{
    protected $guarded = [];

    public function getClient() : ClientInterface
    {
        return resolve(CommentClient::class);
    }
}


$comment = Comment::indexComments()
    ->withResponseKey('data')
    ->filterResponseItem(function ($item) {
        return ['id' => $item['id']];
    })->get()->first();
    
//App\RemoteModels\Comment
```

Client and Model classes are proxies for `Illuminate\Http\Client\PendingReuqest`. You can use all http client methods
```php
$comment = Comment::indexComments()
    ...
    ->withHeaders(['X-Foo' => 'X-Baz'])   
//App\RemoteModels\Comment
```

Detail information about laravel http client [here](https://laravel.com/docs/7.x/http-client)

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email sidigicoder@gmail.com or use the issue tracker.

## Credits

-   [Sidigi](https://github.com/sidigi)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
