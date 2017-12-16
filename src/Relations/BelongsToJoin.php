<?php

namespace Fico7489\Laravel\EloquentJoin\Relations;

use Fico7489\Laravel\EloquentJoin\Traits\JoinRelationTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BelongsToJoin extends BelongsTo
{
    use JoinRelationTrait;
}
