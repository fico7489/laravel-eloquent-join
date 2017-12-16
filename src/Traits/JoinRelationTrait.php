<?php

namespace Fico7489\Laravel\EloquentJoin\Traits;

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

        if ($method == 'where') {
            parent::__call('setWhereForJoin', $parameters);
        } elseif ($method == 'orWhere') {
            parent::__call('setOrWhereForJoin', $parameters);
        } elseif (in_array($method, $softDeleteOptions)) {
            parent::__call('setSoftDelete', $parameters);
        } else {
            parent::__call('scopeSetInvalidJoin', $method, $parameters);
        }

        return parent::__call($method, $parameters);
    }
}
