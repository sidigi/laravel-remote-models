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


$comments = Comment::indexComments()->filterResponseItem(function ($item) {
            return ['id' => $item['comment_id']];
        })->get())
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email sidigi@gmail.com or use the issue tracker.

## Credits

-   [Sidigi](https://github.com/sidigi)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
