<?php

namespace Sidigi\LaravelRemoteModels\Tests;

use PHPUnit\Framework\TestCase;
use Sidigi\LaravelRemoteModels\Model;

class ExampleTest extends TestCase
{
    /** @test */
    public function true_is_true()
    {
        $model = new EloquentModelStub;
        dd(EloquentModelStub::filter('id', 1));
    }
}

class EloquentModelStub extends Model
{
    protected $guarded = [];
}
