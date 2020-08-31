<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Options
    |--------------------------------------------------------------------------
    |
    | This option controls the default response key
    |
    */
    'defaults' => [
        'pagination_strategy' => 'page_based',
    ],

    'pagination_strategies' => [
        'page_based' => [
            'class' => Sidigi\LaravelRemoteModels\JsonApi\Pagination\PageBasedStrategy::class,
            'response_number_key' => 'meta.page_count',
            'defaults' => [
                'number' => 1,
                'size' => 100,
            ],
        ],
        'cursor_based' => [
            'class' => Sidigi\LaravelRemoteModels\JsonApi\Pagination\CursorBasedStrategy::class,
            'defaults' => [
                'limit' => 100,
                'before' => '',
                'after' => '',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Remote Clients
    |--------------------------------------------------------------------------
    |
    |    'comment-client' => [
    |        'client' =>  App\Clients\CommentClient::class,
    |        'base_uri' => 'base uri',
    |        'pagination_strategy' => 'page-based'  //cursor-based / Page-based
    |        'paths' => [
    |            'index_comments' => 'comments',
    |            'index_comments_by_post' => '/comments?postId={id}',
    |        ],
    |    ],
    |
    |
    */
    'clients' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Remote Models
    |--------------------------------------------------------------------------
    |
    |   App\RemoteModels\Comment::class => 'comment-client',
    |   //or
    |   App\RemoteModels\Comment::class => App\Clients\CommentClient::class,
    |
    */
    'models' => [

    ],
];
