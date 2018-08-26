<?php

namespace Fico7489\Laravel\EloquentJoin\Traits;

use Fico7489\Laravel\EloquentJoin\EloquentJoinBuilder;

trait EloquentJoin
{
    use ExtendRelationsTrait;

    public function newEloquentBuilder($query)
    {
        return new EloquentJoinBuilder($query);
    }
}
