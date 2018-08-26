<?php

namespace Fico7489\Laravel\EloquentJoin\Exceptions;

class InvalidRelationWhere extends \Exception
{
    public $message = 'Package allows only following where(orWhere) clauses type on relation : ->where($column, $operator, $value) and ->where([$column => $value]).';
}
