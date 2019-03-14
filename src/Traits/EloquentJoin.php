<?php

namespace Fico7489\Laravel\EloquentJoin\Traits;

use Fico7489\Laravel\EloquentJoin\EloquentJoinBuilder;

trait EloquentJoin
{
    public function newEloquentBuilder($query)
    {
        $newEloquentBuilder = new EloquentJoinBuilder($query);

        if (isset($this->useTableAlias)) {
            $newEloquentBuilder->setUseTableAlias($this->useTableAlias);
        }

        if (isset($this->appendRelationsCount)) {
            $newEloquentBuilder->setAppendRelationsCount($this->appendRelationsCount);
        }

        if (isset($this->leftJoin)) {
            $newEloquentBuilder->setLeftJoin($this->leftJoin);
        }

        if (isset($this->aggregateMethod)) {
            $newEloquentBuilder->setAggregateMethod($this->aggregateMethod);
        }

        return $newEloquentBuilder;
    }
}
