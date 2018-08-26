<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Scope;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TestExceptionScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('test', '=', 'test');
    }
}
