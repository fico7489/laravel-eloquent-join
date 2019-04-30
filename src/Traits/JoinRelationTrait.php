<?php

namespace Fico7489\Laravel\EloquentJoin\Traits;

use Fico7489\Laravel\EloquentJoin\EloquentJoinBuilder;

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
        if ($this->getQuery() instanceof EloquentJoinBuilder) {
            $this->getQuery()->relationClauses[] = [$method => $parameters];
        }

        return parent::__call($method, $parameters);
    }
}
