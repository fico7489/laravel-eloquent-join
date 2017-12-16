<?php

namespace Fico7489\Laravel\EloquentJoin\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Seller;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\Models\OrderItem;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Location;

class EloquentJoinTraitTest extends TestCase
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
    }

    private function fetchQuery()
    {
        $log = \DB::getQueryLog();
        return end($log)['query'];
    }

    private function checkOrder($items, $order, $count)
    {
        $this->assertEquals($order[0], $items->get(0)->id);
        $this->assertEquals($order[1], $items->get(1)->id);
        $this->assertEquals($order[2], $items->get(2)->id);
        $this->assertEquals($count, $items->count());
    }

    public function testOrderByJoinWithoutJoining()
    {
        $items = OrderItem::orderByJoin('name')->get();
        $this->checkOrder($items, [1, 2, 3], 3);

        OrderItem::find(2)->update(['name' => 9]);
        $items = OrderItem::orderByJoin('name')->get();
        $this->checkOrder($items, [1, 3, 2], 3);

        $items = OrderItem::orderByJoin('name', 'desc')->get();
        $this->checkOrder($items, [2, 3, 1], 3);
    }

    public function testOrderByJoinJoinFirstRelation()
    {
        $items = OrderItem::orderByJoin('order.number')->get();
        $this->checkOrder($items, [1, 2, 3], 3);

        Order::find(2)->update(['number' => 9]);
        $items = OrderItem::orderByJoin('order.number')->get();
        $this->checkOrder($items, [1, 3, 2], 3);

        $items = OrderItem::orderByJoin('order.number', 'desc')->get();
        $this->checkOrder($items, [2, 3, 1], 3);

        OrderItem::create(['name' => '4', 'order_id' => null]);
        $items = OrderItem::orderByJoin('order.number', 'desc')->get();
        $this->checkOrder($items, [2, 3, 1], 4);

        $items = OrderItem::orderByJoin('order.number', 'asc')->get();
        $this->checkOrder($items, [4, 1, 3], 4);
    }

    public function testOrderByJoinJoinSecondRelation()
    {
        $items = OrderItem::orderByJoin('order.seller.title')->get();
        $this->checkOrder($items, [1, 2, 3], 3);

        $items = OrderItem::orderByJoin('order.seller.title', 'desc')->get();
        $this->checkOrder($items, [3, 2, 1], 3);

        Seller::find(2)->update(['title' => 9]);
        $items = OrderItem::orderByJoin('order.seller.title', 'desc')->get();
        $this->checkOrder($items, [2, 3, 1], 3);

        OrderItem::create(['name' => '4', 'order_id' => null]);
        $items = OrderItem::orderByJoin('order.seller.title', 'asc')->get();
        $this->checkOrder($items, [4, 1, 3], 4);
    }

    public function testOrderByJoinJoinThirdRelationHasOne()
    {
        $items = OrderItem::orderByJoin('order.seller.location.address')->get();
        $this->checkOrder($items, [1, 2, 3], 3);

        $items = OrderItem::orderByJoin('order.seller.location.address', 'desc')->get();
        $this->checkOrder($items, [3, 2 , 1], 3);

        Location::find(2)->update(['address' => 9]);
        $items = OrderItem::orderByJoin('order.seller.location.address', 'desc')->get();
        $this->checkOrder($items, [2, 3 , 1], 3);
    }

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

    public function testSoftDeleteHasNotRelated()
    {
        //default (withoutTrashed)
        \DB::enableQueryLog();
        $items = OrderItem::orderByJoin('name')->get();
        $queryTest = '/select "order_items".* from "order_items" where "order_items"."deleted_at" is null group by "order_items"."id" order by "order_items"."name" asc/';
        $this->assertRegExp($queryTest, $this->fetchQuery());

        //withoutTrashed
        \DB::enableQueryLog();
        $items = OrderItem::orderByJoin('name')->withoutTrashed()->get();
        $queryTest = '/select "order_items".* from "order_items" where "order_items"."deleted_at" is null group by "order_items"."id" order by "order_items"."name" asc/';
        $this->assertRegExp($queryTest, $this->fetchQuery());

        //onlyTrashed
        \DB::enableQueryLog();
        $items = OrderItem::orderByJoin('name')->onlyTrashed()->get();
        $queryTest = '/select "order_items".* from "order_items" where "order_items"."deleted_at" is not null group by "order_items"."id" order by "order_items"."name" asc/';
        $this->assertRegExp($queryTest, $this->fetchQuery());

        //withTrashed
        \DB::enableQueryLog();
        $items = OrderItem::orderByJoin('name')->withTrashed()->get();
        $queryTest = '/select "order_items".* from "order_items" group by "order_items"."id" order by "order_items"."name" asc/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testSoftDeleteHasRelated()
    {
        //default (withoutTrashed)
        \DB::enableQueryLog();
        $items = OrderItem::orderByJoin('order.number')->get();
        $queryTest = '/select "order_items".* from "order_items" left join "orders" as "(.*)" on "(.*)"."id" = "order_items"."order_id" where "(.*)"."deleted_at" is null and "order_items"."deleted_at" is null group by "order_items"."id/';
        $this->assertRegExp($queryTest, $this->fetchQuery());

        //withoutTrashed
        \DB::enableQueryLog();
        $items = OrderItem::orderByJoin('order.number')->withoutTrashed()->get();
        $queryTest = '/select "order_items".* from "order_items" left join "orders" as "(.*)" on "(.*)"."id" = "order_items"."order_id" where "(.*)"."deleted_at" is null and "order_items"."deleted_at" is null group by "order_items"."id/';
        $this->assertRegExp($queryTest, $this->fetchQuery());

        //onlyTrashed
        \DB::enableQueryLog();
        $items = OrderItem::orderByJoin('order.number')->onlyTrashed()->get();
        $queryTest = '/select "order_items".* from "order_items" left join "orders" as "(.*)" on "(.*)"."id" = "order_items"."order_id" where "(.*)"."deleted_at" is null and "order_items"."deleted_at" is not null group by "order_items"."id/';
        $this->assertRegExp($queryTest, $this->fetchQuery());

        //withTrashed
        \DB::enableQueryLog();
        $items = OrderItem::orderByJoin('order.number')->withTrashed()->get();
        $queryTest = '/select "order_items".* from "order_items" left join "orders" as "(.*)" on "(.*)"."id" = "order_items"."order_id" where "(.*)"."deleted_at" is null group by "order_items"."id/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testWhereOnRelationWithOrderByJoin()
    {
        //location have two where  ['is_primary => 0', 'is_secondary' => 0]
        \DB::enableQueryLog();
        $items = Seller::orderByJoin('location.id', 'desc')->get();
        $queryTest = '/select "sellers".* from "sellers" left join "locations" as "(.*)" on "(.*)"."seller_id" = "sellers"."id" where "(.*)"."deleted_at" is null and "is_primary" = \? and "is_secondary" = \? group by "sellers"."id" order by "(.*)"."id" desc/';
        $this->assertRegExp($queryTest, $this->fetchQuery());

        //locationPrimary have one where ['is_primary => 1']
        \DB::enableQueryLog();
        $items = Seller::orderByJoin('locationPrimary.id', 'desc')->get();
        $queryTest = '/select "sellers".* from "sellers" left join "locations" as "(.*)" on "(.*)"."seller_id" = "sellers"."id" where "(.*)"."deleted_at" is null and "is_primary" = \? group by "sellers"."id" order by "(.*)"."id" desc/';
        $this->assertRegExp($queryTest, $this->fetchQuery());

        //locationPrimary have one where ['is_secondary => 1']
        \DB::enableQueryLog();
        $items = Seller::orderByJoin('locationSecondary.id', 'desc')->get();
        $queryTest = '/select "sellers".* from "sellers" left join "locations" as "(.*)" on "(.*)"."seller_id" = "sellers"."id" where "(.*)"."deleted_at" is null and "is_secondary" = \? group by "sellers"."id" order by "(.*)"."id" desc/';
        $this->assertRegExp($queryTest, $this->fetchQuery());

        //locationPrimary have one where ['is_primary => 1'] and one orWhere ['is_secondary => 1']
        \DB::enableQueryLog();
        $items = Seller::orderByJoin('locationPrimaryOrSecondary.id', 'desc')->get();
        $queryTest = '/select "sellers".* from "sellers" left join "locations" as "(.*)" on "(.*)"."seller_id" = "sellers"."id" where \("(.*)"."deleted_at" is null and "is_primary" = \? or "is_secondary" = \?\) group by "sellers"."id" order by "(.*)"."id" desc/';
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
