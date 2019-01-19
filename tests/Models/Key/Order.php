<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models\Key;

use Fico7489\Laravel\EloquentJoin\Tests\Models\BaseModel;

class Order extends BaseModel
{
    protected $primaryKey = 'key_id_order';

    protected $table = 'key_orders';

    protected $fillable = ['number', 'seller_id'];

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'key_seller_id');
    }
}
