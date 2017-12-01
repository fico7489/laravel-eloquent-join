<?php

namespace Fico7489\Laravel\SortJoin\Tests\Models;

class OrderItem extends BaseModel
{
    protected $table = 'order_items';

    protected $fillable = ['name'];
	
	public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
