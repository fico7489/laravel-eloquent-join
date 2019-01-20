<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models\Key;

use Fico7489\Laravel\EloquentJoin\Tests\Models\BaseModel;

class Order extends BaseModel
{
    protected $primaryKey = 'id_order_primary';

    protected $table = 'key_orders';

    protected $fillable = ['number', 'id_seller_foreign'];

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'id_seller_foreign', 'id_seller_primary');
    }

    public function sellerOwner()
    {
        return $this->belongsTo(Seller::class, 'id_seller_foreign', 'id_seller_owner');
    }
}
