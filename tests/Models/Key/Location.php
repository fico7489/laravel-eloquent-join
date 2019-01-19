<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models\Key;

use Fico7489\Laravel\EloquentJoin\Tests\Models\BaseModel;

class Location extends BaseModel
{
    protected $primaryKey = 'key_id_location';

    protected $table = 'key_locations';

    protected $fillable = ['address', 'seller_id'];
}
