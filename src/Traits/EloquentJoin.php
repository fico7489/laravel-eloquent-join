<?php

namespace Fico7489\Laravel\EloquentJoin\Traits;

use Fico7489\Laravel\EloquentJoin\EloquentJoinBuilder;
use Illuminate\Database\Eloquent\Builder;

trait EloquentJoin
{
    use ExtendRelationsTrait;


    //set invalid clauses on join relations
    public function scopeSetInvalidJoin(Builder $builder, $method, $parameters = [])
    {
        $this->relationNotAllowedClauses[$method] = $method;
    }

    //set where clause for join relations
    public function scopeSetWhereForJoin(Builder $builder, $column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->relationWhereClauses[] = ['column' => $column, 'operator' => $operator, 'value' => $value, 'boolean' => $boolean];
    }

    //set orWhere clause for join relations
    public function scopeSetOrWhereForJoin(Builder $builder, $column, $operator = null, $value)
    {
        $this->relationWhereClauses[] = ['column' => $column, 'operator' => $operator, 'value' => $value, 'boolean' => 'or'];
    }

    //set soft delete clauses for join relations
    public function scopeSetSoftDelete(Builder $builder, $param)
    {
        $this->softDelete = $param;
    }

    public function newEloquentBuilder($query)
    {
        return new EloquentJoinBuilder($query);
    }
}
