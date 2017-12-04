<?php

namespace Fico7489\Laravel\SortJoin\Traits;

trait JoinRelationTrait
{
    /**
     * Handle dynamic method calls to the relationship.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $method = $method == 'where' ? 'whereJoin' : $method;
        $method = $method == 'orWhere' ? 'orWhereJoin' : $method;

        if( ! in_array($method, ['whereJoin', 'orWhereJoin'])){
            throw new \Exception('Only where and orWhere are allowed on Join relations.');
        }

        return parent::__call($method, $parameters);
    }
}
