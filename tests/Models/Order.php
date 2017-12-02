<?php

namespace Fico7489\Laravel\SortJoin\Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends BaseModel
{
    use SoftDeletes;

    protected $table = 'orders';

    protected $fillable = ['number', 'seller_id'];
	
	public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
