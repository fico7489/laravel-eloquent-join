<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\OrderItem;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class SoftDeleteTest extends TestCase
{
    public function testNotRelatedWithoutTrashedDefault()
    {
        OrderItem::orderByJoin('name')->get();
        $queryTest = 'select * 
            from "order_items" 
            where "order_items"."deleted_at" is null 
            order by "order_items"."name" asc';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testNotRelatedWithoutTrashedExplicit()
    {
        OrderItem::orderByJoin('name')->withoutTrashed()->get();
        $queryTest = 'select * 
            from "order_items"
            where "order_items"."deleted_at" is null
            order by "order_items"."name" asc';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testNotRelatedOnlyTrashedExplicit()
    {
        OrderItem::orderByJoin('name')->onlyTrashed()->get();
        $queryTest = 'select * 
            from "order_items" 
            where "order_items"."deleted_at" is not null
            order by "order_items"."name" asc';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testNotRelatedWithTrashedExplicit()
    {
        OrderItem::orderByJoin('name')->withTrashed()->get();
        $queryTest = 'select * 
            from "order_items" 
            order by "order_items"."name" asc';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testRelatedWithoutTrashedDefault()
    {
        OrderItem::orderByJoin('order.number')->get();
        $queryTest = 'select order_items.*, MAX("orders"."number") as sort
            from "order_items" left join "orders" 
            on "orders"."id" = "order_items"."order_id" 
            and "orders"."deleted_at" is null
            where "order_items"."deleted_at" is null 
            group by "order_items"."id"
            order by sort asc';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testRelatedWithoutTrashedExplicit()
    {
        OrderItem::orderByJoin('order.number')->withoutTrashed()->get();
        $queryTest = 'select order_items.*, MAX("orders"."number") as sort
            from "order_items" 
            left join "orders" 
            on "orders"."id" = "order_items"."order_id" 
            and "orders"."deleted_at" is null 
            where "order_items"."deleted_at" is null 
            group by "order_items"."id"
            order by sort asc';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testRelatedOnlyTrashedExplicit()
    {
        OrderItem::orderByJoin('order.number')->onlyTrashed()->get();
        $queryTest = 'select order_items.*, MAX("orders"."number") as sort
            from "order_items" 
            left join "orders" 
            on "orders"."id" = "order_items"."order_id" 
            and "orders"."deleted_at" is null 
            where "order_items"."deleted_at" is not null
            group by "order_items"."id"
            order by sort asc';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testRelatedWithTrashedExplicit()
    {
        OrderItem::orderByJoin('order.number')->withTrashed()->get();
        $queryTest = 'select order_items.*, MAX("orders"."number") as sort
            from "order_items" 
            left join "orders" 
            on "orders"."id" = "order_items"."order_id" 
            and "orders"."deleted_at" is null 
            group by "order_items"."id"
            order by sort asc';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testRelatedWithTrashedOnRelation()
    {
        OrderItem::orderByJoin('orderWithTrashed.number')->get();
        $queryTest = 'select order_items.*, MAX("orders"."number") as sort
            from "order_items" 
            left join "orders" 
            on "orders"."id" = "order_items"."order_id" 
            where "order_items"."deleted_at" is null
            group by "order_items"."id"
            order by sort asc';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testRelatedOnlyTrashedOnRelation()
    {
        OrderItem::orderByJoin('orderOnlyTrashed.number')->get();
        $queryTest = 'select order_items.*, MAX("orders"."number") as sort
            from "order_items"
            left join "orders" 
            on "orders"."id" = "order_items"."order_id"
            and "orders"."deleted_at" is not null 
            where "order_items"."deleted_at" is null 
            group by "order_items"."id"
            order by sort asc';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
