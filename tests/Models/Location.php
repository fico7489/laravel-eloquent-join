<?php

namespace Fico7489\Laravel\SortJoin\Tests\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends BaseModel
{
    protected $table = 'locations';

    protected $fillable = ['address', 'seller_id'];

    public function seller()
    {
        return $this->belongsTo(sSeller::class);
    }
}
