<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models\Key;

use Fico7489\Laravel\EloquentJoin\Tests\Models\BaseModel;

class Order extends BaseModel
{
    protected $primaryKey = 'id';

    protected $table = 'key_orders';

    protected $fillable = ['secondary_key_seller', 'number'];

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'secondary_key_seller', 'secondary_key');
    }
}
