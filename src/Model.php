<?php

namespace Sidigi\LaravelRemoteModels;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel implements ModelInterface
{
    public function newQuery()
    {
        return (new Builder)->setModel($this);
    }
}
