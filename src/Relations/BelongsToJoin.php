<?php

namespace Fico7489\Laravel\SortJoin\Relations;

use Fico7489\Laravel\SortJoin\Traits\JoinRelationTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BelongsToJoin extends HasOne
{
    use JoinRelationTrait;
}
