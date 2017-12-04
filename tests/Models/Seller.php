<?php

namespace Fico7489\Laravel\SortJoin\Tests\Models;

class Seller extends BaseModel
{
    protected $table = 'sellers';

    protected $fillable = ['title', 'deleted_at'];

	public function location()
    {
        return $this->hasOneJoin(Location::class)
            ->whereJoin('is_primary', '=', 0)
            ->whereJoin('is_secondary', '=', 0);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function locationPrimary()
    {
        return $this->hasOneJoin(Location::class)
            ->whereJoin('is_primary', '=', 1);
    }

    public function locationSecondary()
    {
        return $this->hasOneJoin(Location::class)
            ->whereJoin('is_secondary', '=', 1);
    }

    public function locationPrimaryOrSecondary()
    {
        return $this->hasOneJoin(Location::class)
            ->whereJoin('is_primary', '=', 1)
            ->orWhereJoin('is_secondary', '=', 1);
    }
}
