<?php

namespace Fico7489\Laravel\SortJoin\Tests\Models;

use Fico7489\Laravel\SortJoin\Traits\SortJoinTrait;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use SortJoinTrait;
}
