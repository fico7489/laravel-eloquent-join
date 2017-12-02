<?php

namespace Fico7489\Laravel\SortJoin\Tests\Models;

class Location extends BaseModel
{
    protected $table = 'locations';

    protected $fillable = ['address', 'seller_id'];

    public function seller()
    {
        return $this->belongsTo(sSeller::class);
    }
}
