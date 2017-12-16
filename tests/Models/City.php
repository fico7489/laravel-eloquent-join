<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class City extends BaseModel
{
    use SoftDeletes;

    protected $table = 'cities';

    protected $fillable = ['name'];
}
