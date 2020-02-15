<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests\Clauses;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class OrderByTest extends TestCase
{
    public function testOrderBy()
    {
        Order::joinRelations('seller')
            ->orderByJoin('seller.id', 'asc')
            ->get();

        $queryTest = 'select orders.*, MAX(?) as sort
            from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            where "orders"."deleted_at" is null 
            group by "orders"."id"
            order by sort asc';
        $bindingsTest = ['sellers.id'];

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
        $this->assertEquals($bindingsTest, $this->fetchBindings());
    }

    public function testOrderByMultiple()
    {
        Order::joinRelations('seller')
            ->orderByJoin('seller.id', 'asc')
            ->orderByJoin('seller.title', 'desc')
            ->get();

        $queryTest = 'select orders.*, MAX(?) as sort, MAX(?) as sort2
            from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            where "orders"."deleted_at" is null 
            group by "orders"."id"
            order by sort asc, sort2 desc';
        $bindingsTest = ['sellers.id', 'sellers.title'];

        $this->assertEquals($bindingsTest, $this->fetchBindings());
        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
