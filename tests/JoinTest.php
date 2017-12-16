<?php

namespace Fico7489\Laravel\EloquentJoin\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Seller;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\Models\OrderItem;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Location;

class JoinTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $seller = Seller::create(['title' => 1]);
        $seller2 = Seller::create(['title' => 2]);
        $seller3 = Seller::create(['title' => 3]);
        Seller::create(['title' => 4]);

        Location::create(['address' => 1, 'seller_id' => $seller->id]);
        Location::create(['address' => 2, 'seller_id' => $seller2->id]);
        Location::create(['address' => 3, 'seller_id' => $seller3->id]);
        Location::create(['address' => 3, 'seller_id' => $seller3->id]);

        Location::create(['address' => 4, 'seller_id' => $seller3->id, 'is_primary' => 1]);
        Location::create(['address' => 5, 'seller_id' => $seller3->id, 'is_secondary' => 1]);

        Order::create(['number' => '1', 'seller_id' => $seller->id]);
        Order::create(['number' => '2', 'seller_id' => $seller2->id]);
        Order::create(['number' => '3', 'seller_id' => $seller3->id]);

        OrderItem::create(['name' => '1', 'order_id' => $seller->id]);
        OrderItem::create(['name' => '2', 'order_id' => $seller2->id]);
        OrderItem::create(['name' => '3', 'order_id' => $seller3->id]);

        $this->startListening();
    }

    private function startListening()
    {
        \DB::enableQueryLog();
    }

    private function fetchQuery()
    {
        $log = \DB::getQueryLog();
        return end($log)['query'];
    }

    public function testwhereJoinNoRelation()
    {
        Seller::whereJoin('title', '=', 'test')->get();

        $queryTest = '/select \* from "sellers" where "sellers"."title" = \?/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testwhereJoinBelongsTo()
    {
        Seller::whereJoin('city.name', '=', 'test')->get();

        $queryTest = '/select "sellers".* from "sellers" left join "cities" on "cities"."id" = "sellers"."city_id" where "cities"."deleted_at" is null and "cities"."name" = \? group by "sellers"."id"/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testwhereJoinHasOne()
    {
        Seller::whereJoin('locationPrimary.address', '=', 'test')->get();

        $queryTest = '/select "sellers".* from "sellers" left join "locations" on "locations"."seller_id" = "sellers"."id" where "locations"."deleted_at" is null and "is_primary" = \? and "locations"."address" = \? group by "sellers"."id"/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }
}
