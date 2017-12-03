<?php

namespace Fico7489\Laravel\SortJoin\Tests\Models;

class Seller extends BaseModel
{
    protected $table = 'sellers';

    protected $fillable = ['title', 'deleted_at'];

	public function location()
    {
        return $this->hasOne(Location::class);
    }

    public function locationPrimary()
    {
        return $this->hasOne(Location::class)
            ->whereJoin('is_primary', '=', 1);
    }

    public function locationSecondary()
    {
        return $this->hasOne(Location::class)
            ->whereJoin('is_secondary', '=', 1);
    }
}
