<?php

use Illuminate\Http\Client\PendingRequest;
use Sidigi\LaravelRemoteModels\Clients\AwsLambda\PendingRequest as AwsPendingRequest;
use Sidigi\LaravelRemoteModels\Pagination\PaginationBaseStrategy;

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
        'response_key'        => 'data',
        'pagination_strategy' => 'page_based',
    ],

    'providers' => [
        'aws-lambda' => [
            'request_class' => AwsPendingRequest::class,
        ],
        'http' => [
            'request_class' => PendingRequest::class,
        ],
    ],

    'pagination_strategies' => [
        'page_based' => [
            'class'               => PaginationBaseStrategy::class,
            'response_number_key' => 'meta.pages_count',
            'defaults'            => [
                'number' => 1,
                'size'   => 100,
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
    |        'response_key' => 'data',
    |        'pagination_strategy' => 'page-based'
    |        'paths' => [
    |            'index_comments' => 'comments',
    |            'index_comments_by_post' => '/comments?postId={id}',
    |        ],
    |    ],
    |    'aws-user-client'    => [
    |        'client'   => App\Clients\UserAwsClient::class,
    |        'function_name' => 'user-service-api',
    |        'provider' => 'aws-lambda',
    |        'base_uri' => env('USER_MICRO_SERVICE_BASE_URL', ''),
    |        'paths'    => [
    |            'me'             => 'me',
    |            'index_user'    => 'users',
    |            'detail_user'    => 'users/{id}',
    |            'detail_company' => 'companies/{id}',
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
