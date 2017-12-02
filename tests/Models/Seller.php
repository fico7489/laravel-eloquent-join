<?php

namespace Fico7489\Laravel\SortJoin\Tests\Models;

class Seller extends BaseModel
{
    protected $table = 'sellers';

    protected $fillable = ['title'];

	public function location()
    {
        return $this->hasOne(Location::class);
    }
}
