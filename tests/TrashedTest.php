<?php

namespace Fico7489\Laravel\EloquentJoin\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\OrderItem;

class TrashedTest extends TestCase
{
    public function testNotRelatedWithoutTrashedDefault()
    {
        OrderItem::orderByJoin('name')->get();
        $queryTest = '/select \* from "order_items" where "order_items"."deleted_at" is null order by "order_items"."name" asc/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testNotRelatedWithoutTrashed()
    {
        OrderItem::orderByJoin('name')->withoutTrashed()->get();
        $queryTest = '/select \* from "order_items" where "order_items"."deleted_at" is null order by "order_items"."name" asc/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testNotRelatedOnlyTrashed()
    {
        OrderItem::orderByJoin('name')->onlyTrashed()->get();
        $queryTest = '/select \* from "order_items" where "order_items"."deleted_at" is not null order by "order_items"."name" asc/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testNotRelatedWithTrashed()
    {
        OrderItem::orderByJoin('name')->withTrashed()->get();
        $queryTest = '/select \* from "order_items" order by "order_items"."name" asc/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testRelatedWithoutTrashedDefault()
    {
        OrderItem::orderByJoin('order.number')->get();
        $queryTest = '/select "order_items".* from "order_items" left join "orders" on "(.*)"."id" = "order_items"."order_id" where "(.*)"."deleted_at" is null and "order_items"."deleted_at" is null group by "order_items"."id/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testRelatedWithoutTrashed()
    {
        OrderItem::orderByJoin('order.number')->withoutTrashed()->get();
        $queryTest = '/select "order_items".* from "order_items" left join "orders" on "(.*)"."id" = "order_items"."order_id" where "(.*)"."deleted_at" is null and "order_items"."deleted_at" is null group by "order_items"."id/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testRelatedOnlyTrashed()
    {
        OrderItem::orderByJoin('order.number')->onlyTrashed()->get();
        $queryTest = '/select "order_items".* from "order_items" left join "orders" on "(.*)"."id" = "order_items"."order_id" where "(.*)"."deleted_at" is null and "order_items"."deleted_at" is not null group by "order_items"."id/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testRelatedWithTrashed()
    {
        OrderItem::orderByJoin('order.number')->withTrashed()->get();
        $queryTest = '/select "order_items".* from "order_items" left join "orders" on "(.*)"."id" = "order_items"."order_id" where "(.*)"."deleted_at" is null group by "order_items"."id/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }
}
