<?php

namespace Fico7489\Laravel\SortJoin\Tests;

use Fico7489\Laravel\SortJoin\Tests\Models\Seller;
use Fico7489\Laravel\SortJoin\Tests\Models\Order;
use Fico7489\Laravel\SortJoin\Tests\Models\OrderItem;
use Fico7489\Laravel\SortJoin\Tests\Models\Location;

class SortJoinTraitTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $seller = Seller::create(['title' => 1]);
        $seller2 = Seller::create(['title' => 2]);
        $seller3 = Seller::create(['title' => 3]);
        $seller4 = Seller::create(['title' => 4]);

        $location = Location::create(['address' => 1, 'seller_id' => $seller->id]);
        $location2 = Location::create(['address' => 2, 'seller_id' => $seller2->id]);
        $location3 = Location::create(['address' => 3, 'seller_id' => $seller3->id]);
        $location4 = Location::create(['address' => 3, 'seller_id' => $seller3->id]);

        $locationPrimary   = Location::create(['address' => 4, 'seller_id' => $seller3->id, 'is_primary' => 1]);
        $locationSecondary = Location::create(['address' => 5, 'seller_id' => $seller3->id, 'is_secondary' => 1]);

        $order = Order::create(['number' => '1', 'seller_id' => $seller->id]);
        $order2 = Order::create(['number' => '2', 'seller_id' => $seller2->id]);
        $order3 = Order::create(['number' => '3', 'seller_id' => $seller3->id]);

        $orderItem = OrderItem::create(['name' => '1', 'order_id' => $seller->id]);
        $orderItem2 = OrderItem::create(['name' => '2', 'order_id' => $seller2->id]);
        $orderItem3 = OrderItem::create(['name' => '3', 'order_id' => $seller3->id]);
    }

    public function testOrderByJoinWithoutJoining()
    {
        //first item is id = 1, last id = 3, count 3
        $items = OrderItem::orderByJoin('name')->get();
        $this->assertEquals(1, $items->first()->id);
        $this->assertEquals(3, $items->last()->id);
        $this->assertEquals(3, $items->count());

        //first item is id = 1, last id = 2, count 3
        OrderItem::find(2)->update(['name' => 9]);
        $items = OrderItem::orderByJoin('name')->get();
        $this->assertEquals(1, $items->first()->id);
        $this->assertEquals(2, $items->last()->id);
        $this->assertEquals(3, $items->count());
    }

    public function testOrderByJoinJoinFirstRelation()
    {
        //first item is id = 1, last id = 3, count 3
        $items = OrderItem::orderByJoin('order.number')->get();
        $this->assertEquals(1, $items->first()->id);
        $this->assertEquals(3, $items->last()->id);
        $this->assertEquals(3, $items->count());

        //first item is id = 1, last id = 3, count 3
        Order::find(2)->update(['number' => 9]);
        $items = OrderItem::orderByJoin('order.number')->get();
        $this->assertEquals(1, $items->first()->id);
        $this->assertEquals(2, $items->last()->id);
        $this->assertEquals(3, $items->count());
        $this->assertEquals(3, $items->count());

        //normal(default) order (id = 1, 2, 3)
        Order::find(1)->update(['number' => 1]);
        Order::find(2)->update(['number' => 2]);
        Order::find(3)->update(['number' => 3]);
        $items = OrderItem::orderByJoin('order.number')->get();
        $this->assertEquals(1, $items->get(0)->id);
        $this->assertEquals(2, $items->get(1)->id);
        $this->assertEquals(3, $items->get(2)->id);
        $this->assertEquals(3, $items->count());

        //reverse order (id = 3, 2, 1)
        Order::find(1)->update(['number' => 3]);
        Order::find(2)->update(['number' => 2]);
        Order::find(3)->update(['number' => 1]);
        $items = OrderItem::orderByJoin('order.number')->get();
        $this->assertEquals(3, $items->get(0)->id);
        $this->assertEquals(2, $items->get(1)->id);
        $this->assertEquals(1, $items->get(2)->id);
        $this->assertEquals(3, $items->count());

        //reverse order, desc sort (id = 1, 2, 3)
        $items = OrderItem::orderByJoin('order.number', 'desc')->get();
        $this->assertEquals(1, $items->get(0)->id);
        $this->assertEquals(2, $items->get(1)->id);
        $this->assertEquals(3, $items->get(2)->id);
        $this->assertEquals(3, $items->count());

        //normal(default) order (id = 4, 1, 2, 3), test left join (one item does not have order)
        Order::find(1)->update(['number' => 1]);
        Order::find(2)->update(['number' => 2]);
        Order::find(3)->update(['number' => 3]);
        OrderItem::create(['name' => '4', 'order_id' => null]);
        $items = OrderItem::orderByJoin('order.number', 'asc')->get();
        $this->assertEquals(4, $items->get(0)->id);
        $this->assertEquals(1, $items->get(1)->id);
        $this->assertEquals(2, $items->get(2)->id);
        $this->assertEquals(3, $items->get(3)->id);
        $this->assertEquals(4, $items->count());

        //reverse order (id = 1, 2, 3, 4), test left join (one item does not have order)
        $items = OrderItem::orderByJoin('order.number', 'desc')->get();
        $this->assertEquals(3, $items->get(0)->id);
        $this->assertEquals(2, $items->get(1)->id);
        $this->assertEquals(1, $items->get(2)->id);
        $this->assertEquals(4, $items->get(3)->id);
        $this->assertEquals(4, $items->count());
    }

    public function testOrderByJoinJoinSecondRelation()
    {
        //normal order (id = 1, 2, 3)
        $items = OrderItem::orderByJoin('order.seller.title')->get();
        $this->assertEquals(1, $items->get(0)->id);
        $this->assertEquals(2, $items->get(1)->id);
        $this->assertEquals(3, $items->get(2)->id);
        $this->assertEquals(3, $items->count());

        //reverse order (id = 3, 2, 1)
        $items = OrderItem::orderByJoin('order.seller.title', 'desc')->get();
        $this->assertEquals(3, $items->get(0)->id);
        $this->assertEquals(2, $items->get(1)->id);
        $this->assertEquals(1, $items->get(2)->id);
        $this->assertEquals(3, $items->count());

        //change order (id = 2, 3, 1)
        Seller::find(2)->update(['title' => 9]);
        $items = OrderItem::orderByJoin('order.seller.title', 'desc')->get();
        $this->assertEquals(2, $items->get(0)->id);
        $this->assertEquals(3, $items->get(1)->id);
        $this->assertEquals(1, $items->get(2)->id);
        $this->assertEquals(3, $items->count());
    }

    public function testOrderByJoinJoinThirdRelationHasOne()
    {
        //normal order (id = 1, 2, 3)
        Location::find(1)->update(['address' => 1]);
        Location::find(2)->update(['address' => 2]);
        Location::find(3)->update(['address' => 3]);
        $items = OrderItem::orderByJoin('order.seller.location.address')->get();
        $this->assertEquals(1, $items->get(0)->id);
        $this->assertEquals(2, $items->get(1)->id);
        $this->assertEquals(3, $items->get(2)->id);
        $this->assertEquals(3, $items->count());

        //desc order (id = 3, 2, 1)
        $items = OrderItem::orderByJoin('order.seller.location.address', 'desc')->get();
        $this->assertEquals(3, $items->get(0)->id);
        $this->assertEquals(2, $items->get(1)->id);
        $this->assertEquals(1, $items->get(2)->id);
        $this->assertEquals(3, $items->count());

        //change order (id = 2, 3 1)
        Location::find(2)->update(['address' => 9]);
        $items = OrderItem::orderByJoin('order.seller.location.address', 'desc')->get();
        $this->assertEquals(2, $items->get(0)->id);
        $this->assertEquals(3, $items->get(1)->id);
        $this->assertEquals(1, $items->get(2)->id);
        $this->assertEquals(3, $items->count());
    }

    public function testOrderByJoinJoinOnlyOnce()
    {
        \DB::enableQueryLog();
        $items = OrderItem::orderByJoin('order.id')
            ->orderByJoin('order.seller.title')
            ->orderByJoin('order.number')
            ->groupBy('order_items.id')->get();

        $query = \DB::getQueryLog()[0]['query'];
        $this->assertEquals(2, substr_count($query, 'left join'));
    }

    public function testWhereJoin()
    {
        Order::find(1)->update(['number' => 'aaaa']);
        Order::find(2)->update(['number' => 'bbbb']);
        Order::find(3)->update(['number' => 'cccc']);

        //test where does not exists
        $items = OrderItem::orderByJoin('order.number')
            ->whereJoin('order.number', '=', 'dddd')
            ->get();
        $this->assertEquals(0, $items->count());

        //test where does exists
        $items = OrderItem::orderByJoin('order.number')
            ->whereJoin('order.number', '=', 'cccc')
            ->get();
        $this->assertEquals(1, $items->count());

        //test more where does not exists
        $items = OrderItem::orderByJoin('order.number')
            ->whereJoin('order.number', '=', 'bbbb')
            ->whereJoin('order.number', '=', 'cccc')
            ->get();
        $this->assertEquals(0, $items->count());

        //test more where with orWhere exists
        $items = OrderItem::orderByJoin('order.number')
            ->whereJoin('order.number', '=', 'bbbb')
            ->orWhereJoin('order.number', '=', 'cccc')
            ->get();
        $this->assertEquals(2, $items->count());

        //test more where with orWhere does not exists
        $items = OrderItem::orderByJoin('order.number')
            ->whereJoin('order.number', '=', 'dddd')
            ->orWhereJoin('order.number', '=', 'eeee')
            ->get();
        $this->assertEquals(0, $items->count());
    }

    public function testSoftDeleteHas()
    {
        $items = OrderItem::orderByJoin('order.number')
            ->whereJoin('order.number', '=', '1')
            ->get();
        $this->assertEquals(1, $items->count());

        Order::find(1)->delete();
        $items = OrderItem::orderByJoin('order.number')
            ->whereJoin('order.number', '=', '1')
            ->get();
        $this->assertEquals(0, $items->count());
    }

    public function testSoftDeleteNotHas()
    {
        $items = OrderItem::orderByJoin('order.seller.title')
            ->whereJoin('order.seller.title', '=', '1')
            ->get();
        $this->assertEquals(1, $items->count());

        Seller::find(1)->update(['deleted_at' => '2017-01-02']);
        $items = OrderItem::orderByJoin('order.seller.title')
            ->whereJoin('order.seller.title', '=', '1')
            ->get();
        $this->assertEquals(1, $items->count());
        $this->assertTrue(Seller::find(1)->deleted_at != null);
    }

    public function testWhereJoinOnRelation(){
        \DB::enableQueryLog();
        $items = Seller::orderByJoin('location.id', 'desc')->get();
        $log = \DB::getQueryLog();
        $query = end($log)['query'];
        $queryTest = '/select "sellers".* from "sellers" left join "locations" as "(.*)" on "(.*)"."seller_id" = "sellers"."id" where \("(.*)"."deleted_at" is null\) group by "sellers"."id" order by "(.*)"."id" desc/';
        $this->assertRegExp($queryTest, $query);

        \DB::enableQueryLog();
        $items = Seller::orderByJoin('locationPrimary.id', 'desc')->get();
        $log = \DB::getQueryLog();
        $query = end($log)['query'];
        //echo $query;exit;
        $queryTest = '/select "sellers".* from "sellers" left join "locations" as "(.*)" on "(.*)"."seller_id" = "sellers"."id" where \("(.*)"."deleted_at" is null\) and "is_primary" = \? group by "sellers"."id" order by "(.*)"."id" desc/';
        $this->assertRegExp($queryTest, $query);

        \DB::enableQueryLog();
        $items = Seller::orderByJoin('locationSecondary.id', 'desc')->get();
        $log = \DB::getQueryLog();
        $query = end($log)['query'];
        $queryTest = '/select "sellers".* from "sellers" left join "locations" as "(.*)" on "(.*)"."seller_id" = "sellers"."id" where \("(.*)"."deleted_at" is null\) and "is_secondary" = \? group by "sellers"."id" order by "(.*)"."id" desc/';
        $this->assertRegExp($queryTest, $query);

        \DB::enableQueryLog();
        $items = Seller::orderByJoin('locationPrimaryOrSecondary.id', 'desc')->get();
        $log = \DB::getQueryLog();
        $query = end($log)['query'];
        $queryTest = '/select "sellers".* from "sellers" left join "locations" as "(.*)" on "(.*)"."seller_id" = "sellers"."id" where \(\("(.*)"."deleted_at" is null\) and "is_primary" = \? or "is_secondary" = \?\) group by "sellers"."id" order by "(.*)"."id" desc/';
        $this->assertRegExp($queryTest, $query);
    }
}