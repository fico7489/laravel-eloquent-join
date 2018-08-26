<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models;

class Seller extends BaseModel
{
    protected $table = 'sellers';

    protected $fillable = ['title', 'deleted_at'];

    public function location()
    {
        return $this->hasOne(Location::class)
            ->where('is_primary', '=', 0)
            ->where('is_secondary', '=', 0);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function locationPrimary()
    {
        return $this->hasOne(Location::class)
            ->where('is_primary', '=', 1);
    }

    public function locationPrimaryInvalid()
    {
        return $this->hasOne(Location::class)
            ->where('is_primary', '=', 1)
            ->orderBy('is_primary');
    }

    public function locationPrimaryInvalid2()
    {
        return $this->hasOne(Location::class)
            ->where(function ($query) {
                return $query->where(['id' => 1]);
            });
    }

    public function locationPrimaryInvalid3()
    {
        return $this->hasOne(LocationWithGlobalScope::class);
    }

    public function locationSecondary()
    {
        return $this->hasOne(Location::class)
            ->where('is_secondary', '=', 1);
    }

    public function locationPrimaryOrSecondary()
    {
        return $this->hasOne(Location::class)
            ->where('is_primary', '=', 1)
            ->orWhere('is_secondary', '=', 1);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
