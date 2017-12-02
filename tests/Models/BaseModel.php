<?php

namespace Fico7489\Laravel\SortJoin\Tests\Models;

use Fico7489\Laravel\SortJoin\SortJoinTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseModel extends Model
{
	use SoftDeletes;
	use SortJoinTrait;
}
