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

        $seller = Seller::create(['title' => 'title']);
        $seller2 = Seller::create(['title' => 'title2']);
        $seller3 = Seller::create(['title' => 'title3']);
        $seller4 = Seller::create(['title' => 'title4']);

        $location = Location::create(['address' => 'address', 'seller_id' => $seller->id]);
        $location2 = Location::create(['address' => 'address2', 'seller_id' => $seller2->id]);
        $location3 = Location::create(['address' => 'address3', 'seller_id' => $seller3->id]);
        $location4 = Location::create(['address' => 'address4', 'seller_id' => $seller3->id]);

        $order = Order::create(['number' => '1', 'seller_id' => $seller->id]);
        $order2 = Order::create(['number' => '2', 'seller_id' => $seller2->id]);
        $order3 = Order::create(['number' => '3', 'seller_id' => $seller3->id]);

        $orderItem = OrderItem::create(['name' => '1', 'order_id' => $seller->id]);
        $orderItem2 = OrderItem::create(['name' => '2', 'order_id' => $seller2->id]);
        $orderItem3 = OrderItem::create(['name' => '3', 'order_id' => $seller3->id]);
        $orderItem4 = OrderItem::create(['name' => '4', 'order_id' => $seller->id]);
    }

    public function test_OrderByJoin()
    {
        $this->assertTrue(true);
    }
}