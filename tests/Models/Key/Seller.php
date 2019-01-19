<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models\Key;

use Fico7489\Laravel\EloquentJoin\Tests\Models\BaseModel;

class Seller extends BaseModel
{
    protected $primaryKey = 'id';

    protected $table = 'key_sellers';

    protected $fillable = ['secondary_key', 'key_id_location', 'title', 'city_id', 'deleted_at'];

    public function orders()
    {
        return $this->hasMany(Order::class, 'secondary_key_seller', 'secondary_key');
    }
}
