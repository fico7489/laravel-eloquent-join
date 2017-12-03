<?php

namespace Fico7489\Laravel\SortJoin\Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends BaseModel
{
    use SoftDeletes;

    protected $table = 'locations';

    protected $fillable = ['address', 'seller_id', 'is_primary', 'is_secondary'];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
