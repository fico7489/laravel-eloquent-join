<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models\Key;

use Fico7489\Laravel\EloquentJoin\Tests\Models\BaseModel;

class Seller extends BaseModel
{
    protected $primaryKey = 'key_id_seller';

    protected $table = 'key_sellers';

    protected $fillable = ['title', 'deleted_at'];
}
