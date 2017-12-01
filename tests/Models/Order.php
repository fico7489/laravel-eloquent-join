<?php

namespace Fico7489\Laravel\SortJoin\Tests\Models;

class Role extends BaseModel
{
    protected $table = 'orders';

    protected $fillable = ['number'];
	
	public function seller()
    {
        return $this->belongsTo(Order::class);
    }
}
