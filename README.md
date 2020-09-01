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

### Config

```php
    'defaults' => [
        'response_key' => 'data',
        'pagination_strategy' => 'page_based',
    ],

    'pagination_strategies' => [
        'page_based' => [
            'class' => Sidigi\LaravelRemoteModels\JsonApi\Pagination\PageBasedStrategy::class,
            'defaults' => [
                'number' => 1,
                'size' => 100,
            ],
        ],
    ],

    'clients' => [
        'comment-client' => [
            'client' =>  App\RemoteClients\CommentClient::class,
            'base_uri' => 'https://jsonplaceholder.typicode.com',
            'pagination_strategy' => 'page_based',
            'paths' => [
                'index_comments' => 'comments',
                'index_comments_filter_by_post' => '/comments?postId={id}',
                'todo_detail' => 'todos/{id}',
            ],
        ],
    ],

    'models' => [
        App\RemoteModels\Comment::class => 'comment-client',
        //or
        App\RemoteModels\Comment::class => App\RemoteClients\CommentClient::class,
    ],
```

### Clients

```php
use Sidigi\LaravelRemoteModels\Client;

class CommentClient extends Client
{
}

$comments = Comment::get();
$comments = Comment::get('/comments');
$comments = Comment::get('/comments/{id}', ['id' => 1]);
$comments = Comment::get('/comments/{id}', ['id' => 1, 'active' => true]);
$comments = Comment::withPath('/comments/{id}', ['id' => 1])->get();
$comments = Comment::withQuery(['active' => true])->get();
$comments = Comment::get(['active' => true]);
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

    public function getClientClass(): string
    {
        return CommentClient::class;
    }
}

$comment = Comment::indexComments()
    ->get() //response with models
    ->mapModel(Comment::class, fn ($item) => ['id' => $item['id']])
    ->first();

//App\RemoteModels\Comment
```

Client and Model classes are proxies for `Illuminate\Http\Client\PendingReuqest`. You can use all http client methods

```php
$comment = Comment::indexComments()
    ->withHeaders(['X-Foo' => 'X-Baz']) //withToken, withAuth, etc.
    ->get() //response with models
    ->mapModel(Comment::class)
    ->first();
//App\RemoteModels\Comment
```

```php
$builder = Comment::indexComments();

foreach ($builder->perPage() as $response) {
    $comments = $response->mapModel(Note::class);
}
```

```php
$builder = Post::index();

foreach ($builder->perPage() as $response) {
    $comments = $response->mapModel(
        Comment::class,
        fn ($item) => ['id' => $item['id']],
        'data.*.comments'
    );
}
```

```php
$builder = Post::index();

foreach ($builder->perPage() as $response) {
    $commentIds = $response->get('data.*.comments.*.id');
}
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
