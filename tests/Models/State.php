<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class State extends BaseModel
{
    use SoftDeletes;

    protected $table = 'states';

    protected $fillable = ['name'];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function states()
    {
        return $this->hasMany(City::class);
    }
}
