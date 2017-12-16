<?php

namespace Fico7489\Laravel\EloquentJoin\Relations;

use Fico7489\Laravel\EloquentJoin\Traits\JoinRelationTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HasOneJoin extends HasOne
{
    use JoinRelationTrait;
}
