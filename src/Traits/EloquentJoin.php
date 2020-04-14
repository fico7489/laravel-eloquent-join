<?php

namespace Fico7489\Laravel\EloquentJoin\Traits;

use Fico7489\Laravel\EloquentJoin\EloquentJoinBuilder;

/**
 * Trait EloquentJoin.
 *
 * @method static EloquentJoinBuilder joinRelations($relations, $leftJoin = null)
 * @method static EloquentJoinBuilder whereJoin($column, $operator, $value, $boolean = 'and')
 * @method static EloquentJoinBuilder orWhereJoin($column, $operator, $value)
 * @method static EloquentJoinBuilder whereInJoin($column, $values, $boolean = 'and', $not = false)
 * @method static EloquentJoinBuilder whereNotInJoin($column, $values, $boolean = 'and')
 * @method static EloquentJoinBuilder orWhereInJoin($column, $values)
 * @method static EloquentJoinBuilder orWhereNotInJoin($column, $values)
 * @method static EloquentJoinBuilder orderByJoin($column, $direction = 'asc', $aggregateMethod = null)
 */
trait EloquentJoin
{
    use ExtendRelationsTrait;

    /**
     * @param $query
     *
     * @return EloquentJoinBuilder
     */
    public function newEloquentBuilder($query)
    {
        $newEloquentBuilder = new EloquentJoinBuilder($query);

        if (isset($this->useTableAlias)) {
            $newEloquentBuilder->setUseTableAlias($this->useTableAlias);
        }

        if (isset($this->useFullPathTableAlias)) {
            $newEloquentBuilder->setUseFullPathTableAlias($this->useFullPathTableAlias);
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
