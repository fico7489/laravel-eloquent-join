<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Seller;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\Models\OrderItem;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class EloquentJoinTraitTest extends TestCase
{
    public function testWhereJoin()
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

    public function testWhereOnRelationWithOrderByJoin()
    {
        //location have two where  ['is_primary => 0', 'is_secondary' => 0]
        \DB::enableQueryLog();
        $items = Seller::orderByJoin('location.id', 'desc')->get();
        $queryTest = '/select "sellers".* from "sellers" left join "locations" on "(.*)"."seller_id" = "sellers"."id" where "(.*)"."deleted_at" is null and "locations"."is_primary" = \? and "locations"."is_secondary" = \? group by "sellers"."id" order by "(.*)"."id" desc/';
        $this->assertRegExp($queryTest, $this->fetchQuery());

        //locationPrimary have one where ['is_primary => 1']
        \DB::enableQueryLog();
        $items = Seller::orderByJoin('locationPrimary.id', 'desc')->get();
        $queryTest = '/select "sellers".* from "sellers" left join "locations" on "(.*)"."seller_id" = "sellers"."id" where "(.*)"."deleted_at" is null and "locations"."is_primary" = \? group by "sellers"."id" order by "(.*)"."id" desc/';
        $this->assertRegExp($queryTest, $this->fetchQuery());

        //locationPrimary have one where ['is_secondary => 1']
        \DB::enableQueryLog();
        $items = Seller::orderByJoin('locationSecondary.id', 'desc')->get();
        $queryTest = '/select "sellers".* from "sellers" left join "locations" on "(.*)"."seller_id" = "sellers"."id" where "(.*)"."deleted_at" is null and "locations"."is_secondary" = \? group by "sellers"."id" order by "(.*)"."id" desc/';
        $this->assertRegExp($queryTest, $this->fetchQuery());

        //locationPrimary have one where ['is_primary => 1'] and one orWhere ['is_secondary => 1']
        \DB::enableQueryLog();
        $items = Seller::orderByJoin('locationPrimaryOrSecondary.id', 'desc')->get();
        $queryTest = '/select "sellers".* from "sellers" left join "locations" on "(.*)"."seller_id" = "sellers"."id" where \("(.*)"."deleted_at" is null and "locations"."is_primary" = \? or "locations"."is_secondary" = \?\) group by "sellers"."id" order by "(.*)"."id" desc/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testWhereOnRelationWithoutOrderByJoin()
    {
        $seller = Seller::find(1);

        \DB::enableQueryLog();
        $seller->locationPrimary;
        $queryTest = '/select \* from "locations" where "locations"."seller_id" = \? and "locations"."seller_id" is not null and "is_primary" = \? and "locations"."deleted_at" is null limit \d/';
        $this->assertRegExp($queryTest, $this->fetchQuery());

        \DB::enableQueryLog();
        $seller->locationPrimary()->where(['is_secondary' => 1])->get();
        $queryTest = '/select \* from "locations" where "locations"."seller_id" = \? and "locations"."seller_id" is not null and "is_primary" = \? and \("is_secondary" = \?\) and "locations"."deleted_at" is null/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }
}
