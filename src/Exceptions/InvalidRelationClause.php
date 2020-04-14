<?php

namespace Fico7489\Laravel\EloquentJoin\Exceptions;

class InvalidRelationClause extends \Exception
{
    public $message = 'Package allows only following clauses on relation : where, orWhere, withTrashed, onlyTrashed and withoutTrashed.';
}
