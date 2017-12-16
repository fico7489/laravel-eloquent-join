<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class City extends BaseModel
{
    use SoftDeletes;

    protected $table = 'cities';

    protected $fillable = ['name'];

    public function state()
    {
        return $this->belongsToJoin(State::class);
    }

    public function zipCodePrimary()
    {
        return $this->hasOneJoin(ZipCode::class)
            ->where('is_primary', '=', 1);
    }
}
