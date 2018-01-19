<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends BaseModel
{
    use SoftDeletes;

    protected $table = 'order_items';

    protected $fillable = ['name', 'order_id'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderWithTrashed()
    {
        return $this->belongsTo(Order::class, 'order_id')
            ->withTrashed();
    }
    public function orderOnlyTrashed()
    {
        return $this->belongsTo(Order::class, 'order_id')
            ->onlyTrashed();
    }
}
