<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends BaseModel
{
    use SoftDeletes;

    protected $table = 'locations';

    protected $fillable = ['address', 'seller_id', 'is_primary', 'is_secondary', 'city_id'];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function locationAddressPrimary()
    {
        return $this->hasOne(LocationAddress::class)
            ->where('is_primary', '=', 1);
    }

    public function integrations()
    {
        return $this->hasMany(Integration::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function tags()
    {
        return $this->morphMany(Tag::class, 'taggable');
    }
}
