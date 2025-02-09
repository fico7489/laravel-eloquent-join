<?php

namespace Fico7489\Laravel\EloquentJoin\Exceptions;

class InvalidRelation extends \Exception
{
    public $message = 'Package allows only following relations : BelongsTo, BelongsToMany, HasOne and HasMany.';
}
