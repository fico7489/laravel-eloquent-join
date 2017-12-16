<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models;

use Fico7489\Laravel\EloquentJoin\Traits\EloquentJoinTrait;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use EloquentJoinTrait;
}
