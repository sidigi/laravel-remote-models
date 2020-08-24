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
    'options' => [
        'response_key' => 'data',
    ],

    /*
    |--------------------------------------------------------------------------
    | Remote Clients
    |--------------------------------------------------------------------------
    |
    |    'comment-client' => [
    |        'client' =>  App\Clients\CommentClient::class,
    |        'base_uri' => 'base uri',
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
