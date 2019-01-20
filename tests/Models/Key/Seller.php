<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models\Key;

use Fico7489\Laravel\EloquentJoin\Tests\Models\BaseModel;

class Seller extends BaseModel
{
    protected $primaryKey = 'id_seller_primary';

    protected $table = 'key_sellers';

    protected $fillable = ['title'];

    public function location()
    {
        return $this->hasOne(Location::class, 'id_seller_foreign', 'id_seller_primary');
    }

    public function locations()
    {
        return $this->hasMany(Location::class, 'id_seller_foreign', 'id_seller_owner');
    }

    public function locationOwner()
    {
        return $this->hasOne(Location::class, 'id_seller_foreign', 'id_seller_primary');
    }

    public function locationsOwner()
    {
        return $this->hasMany(Location::class, 'id_seller_foreign', 'id_seller_owner');
    }
}
