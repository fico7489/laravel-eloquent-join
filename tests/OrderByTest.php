<?php

namespace Fico7489\Laravel\EloquentJoin\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Location;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\Models\OrderItem;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Seller;

class OrderByTest extends TestCase
{
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
}
