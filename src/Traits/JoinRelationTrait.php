<?php

namespace Fico7489\Laravel\EloquentJoin\Traits;

use Fico7489\Laravel\EloquentJoin\Services\QueryNormalizer;

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
        $this->getQuery()->relationClauses[] = [$method => $parameters];

        return parent::__call($method, $parameters);
    }
}
