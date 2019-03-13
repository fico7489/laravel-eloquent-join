<?php

namespace Fico7489\Laravel\EloquentJoin\Relations;

use Fico7489\Laravel\EloquentJoin\Traits\JoinRelationTrait;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class MorphOneJoin extends MorphOne
{
    use JoinRelationTrait;
}
