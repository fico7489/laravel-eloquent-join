<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class City extends BaseModel
{
    use SoftDeletes;

    protected $table = 'cities';

    protected $fillable = ['name'];

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function zipCodePrimary()
    {
        return $this->hasOne(ZipCode::class)
            ->where('is_primary', '=', 1);
    }

    public function sellers()
    {
        return $this->belongsToMany(Seller::class, 'locations', 'city_id', 'seller_id');
    }

    public function zipCodes()
    {
        return $this->hasMany(ZipCode::class);
    }
}
