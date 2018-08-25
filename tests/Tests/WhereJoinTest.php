<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\Models\OrderItem;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Seller;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class WhereJoinTest extends TestCase
{
    public function testWhereJoinNoRelation()
    {
        Seller::whereJoin('title', '=', 'test')->get();

        $queryTest = '/select \* from "sellers" 
            where "sellers"."title" = \?/';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testWhereJoinBelongsTo()
    {
        Seller::whereJoin('city.name', '=', 'test')->get();

        $queryTest = '/select "sellers".* from "sellers" 
            left join "cities" 
            on "cities"."id" = "sellers"."city_id" 
            and "cities"."deleted_at" is null 
            where "cities"."name" = \?/';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testWhereJoinHasOne()
    {
        Seller::whereJoin('locationPrimary.address', '=', 'test')->get();

        $queryTest = '/select "sellers".* from "sellers" 
            left join "locations" 
            on "locations"."seller_id" = "sellers"."id"
            and "locations"."is_primary" = \? 
            and "locations"."deleted_at" is null 
            and locations.id = \(
                SELECT min\(id\)
                FROM locations
                WHERE locations.seller_id = sellers.id
                LIMIT 1
           \)
            where "locations"."address" = \?/';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testWhereJoinBelongsToBelongsTo()
    {
        Seller::whereJoin('city.state.name', '=', 'test')->get();

        $queryTest = '/select "sellers".* from "sellers" 
            left join "cities" 
            on "cities"."id" = "sellers"."city_id" 
            and "cities"."deleted_at" is null 
            left join "states" on "states"."id" = "cities"."state_id" 
            and "states"."deleted_at" is null 
            where "states"."name" = \?/';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testWhereJoinBelongsToHasOne()
    {
        Seller::whereJoin('city.zipCodePrimary.name', '=', 'test')->get();

        $queryTest = '/select "sellers".* from "sellers" left join "cities" 
            on "cities"."id" = "sellers"."city_id" 
            and "cities"."deleted_at" is null 
            left join "zip_codes" 
            on "zip_codes"."city_id" = "cities"."id" 
            and "zip_codes"."is_primary" = \? 
            and "zip_codes"."deleted_at" is null 
            and zip_codes.id = \(
                SELECT min\(id\)
                FROM zip_codes
                WHERE zip_codes.city_id = cities.id
                LIMIT 1
           \)
            where "zip_codes"."name" = \?/';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testWhereJoinHasOneHasOne()
    {
        Seller::whereJoin('locationPrimary.locationAddressPrimary.name', '=', 'test')->get();

        $queryTest = '/select "sellers".* from "sellers" 
            left join "locations" 
            on "locations"."seller_id" = "sellers"."id" 
            and "locations"."is_primary" = \? 
            and "locations"."deleted_at" is null 
            and locations.id = \(
                SELECT min\(id\)
                FROM locations
                WHERE locations.seller_id = sellers.id
                LIMIT 1
           \)
            left join "location_addresses" on "location_addresses"."location_id" = "locations"."id" 
            and "location_addresses"."is_primary" = \? 
            and "location_addresses"."deleted_at" is null 
            and location_addresses.id = \(
                SELECT min\(id\)
                FROM location_addresses
                WHERE location_addresses.location_id = locations.id
                LIMIT 1
           \)
            where "location_addresses"."name" = \?/';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testWhereJoinHasBelongsTo()
    {
        Seller::whereJoin('locationPrimary.city.name', '=', 'test')->get();

        $queryTest = '/select "sellers".* from "sellers" 
            left join "locations" 
            on "locations"."seller_id" = "sellers"."id" 
            and "locations"."is_primary" = \? 
            and "locations"."deleted_at" is null 
            and locations.id = \(
                SELECT min\(id\)
                FROM locations
                WHERE locations.seller_id = sellers.id
                LIMIT 1
           \)
            left join "cities" 
            on "cities"."id" = "locations"."city_id" 
            and "cities"."deleted_at" is null 
            where "cities"."name" = \?/';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testWhereJoinGeneral()
    {
        Order::find(1)->update(['number' => 'aaaa']);
        Order::find(2)->update(['number' => 'bbbb']);
        Order::find(3)->update(['number' => 'cccc']);

        //test where does not exists
        $items = OrderItem::orderByJoin('order.number')->whereJoin('order.number', '=', 'dddd')->get();
        $this->assertEquals(0, $items->count());

        //test where does exists
        $items = OrderItem::orderByJoin('order.number')->whereJoin('order.number', '=', 'cccc')->get();
        $this->assertEquals(1, $items->count());

        //test where does exists, without orderByJoin
        $items = OrderItem::whereJoin('order.number', '=', 'cccc')->get();
        $this->assertEquals(1, $items->count());

        //test more where does not exists
        $items = OrderItem::orderByJoin('order.number')->whereJoin('order.number', '=', 'bbbb')->whereJoin('order.number', '=', 'cccc')->get();
        $this->assertEquals(0, $items->count());

        //test more where with orWhere exists
        $items = OrderItem::orderByJoin('order.number')->whereJoin('order.number', '=', 'bbbb')->orWhereJoin('order.number', '=', 'cccc')->get();
        $this->assertEquals(2, $items->count());

        //test more where with orWhere does not exists
        $items = OrderItem::orderByJoin('order.number')->whereJoin('order.number', '=', 'dddd')->orWhereJoin('order.number', '=', 'eeee')->get();
        $this->assertEquals(0, $items->count());
    }
}
