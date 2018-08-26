<?php

namespace Fico7489\Laravel\EloquentJoin\Traits;

trait JoinRelationTrait
{
    /**
     * Handle dynamic method calls to the relationship.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $this->getQuery()->relationClauses[] = [$method => $parameters];

        return parent::__call($method, $parameters);
    }
}
