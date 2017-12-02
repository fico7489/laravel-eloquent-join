<?php

namespace Fico7489\Laravel\SortJoin\Tests\Models;

class Order extends BaseModel
{
    protected $table = 'orders';

    protected $fillable = ['number', 'seller_id'];
	
	public function seller()
    {
        return $this->belongsTo(Order::class);
    }
}
