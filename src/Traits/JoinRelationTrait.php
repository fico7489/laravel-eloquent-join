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
        $softDeleteOptions = ['withTrashed', 'withoutTrashed', 'onlyTrashed'];
        $allowedOptions = array_merge(['where', 'orWhere'], $softDeleteOptions);

        if ($method == 'where') {
            parent::__call('setWhereForJoin', $parameters);
        } elseif ($method == 'orWhere') {
            parent::__call('setOrWhereForJoin', $parameters);
        }


        if (in_array($method, $softDeleteOptions)) {
            parent::__call('setSoftDelete', $parameters);
        }

        if (! in_array($method, $allowedOptions)) {
            throw new \Exception('Only allowed clauses on Join relations : ' . implode(', ', $allowedOptions));
        }

        return parent::__call($method, $parameters);
    }
}
