<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Location;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Seller;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class HasOneLeftJoinTest extends TestCase
{
    public function testLeftJoinMore()
    {
        Seller::where('id', '>', 0)->forceDelete();
        Location::where('id', '>', 0)->forceDelete();

        $items = Seller::orderByJoin('location.address')->get();
        $this->assertEquals(0, $items->count());

        $seller = Seller::create(['title' => 'test']);
        Location::create(['address' => 'test', 'seller_id' => $seller->id, 'is_primary' => 1]);
        Location::create(['address' => 'test2', 'seller_id' => $seller->id, 'is_primary' => 1]);

        $items = Seller::orderByJoin('locationPrimary.address')->get();
        $this->assertEquals(1, $items->count());
        $this->assertEquals(1, Seller::count());
    }

    public function testLeftJoinEmpty()
    {
        Seller::where('id', '>', 0)->forceDelete();
        Location::where('id', '>', 0)->forceDelete();

        $items = Seller::orderByJoin('location.address')->get();
        $this->assertEquals(0, $items->count());

        $seller = Seller::create(['title' => 'test']);

        $items = Seller::orderByJoin('locationPrimary.address')->get();
        $this->assertEquals(1, $items->count());
        $this->assertEquals(1, Seller::count());
    }
}
