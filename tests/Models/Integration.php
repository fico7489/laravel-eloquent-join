<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Integration extends BaseModel
{
    use SoftDeletes;

    protected $table = 'integrations';

    protected $fillable = ['name'];
}
