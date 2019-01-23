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

        $queryTest = 'select orders.*, MAX(sellers.id) as sort0
            from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            where "orders"."deleted_at" is null 
            group by "orders"."id"
            order by sort0 asc';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testMultipleOrderBy()
    {
        Order::joinRelations('seller')
            ->orderByJoin('seller.id', 'asc')
            ->orderByJoin('seller.title', 'desc')
            ->get();

        $queryTest = 'select orders.*, MAX(sellers.id) as sort0, MAX(sellers.title) as sort1
            from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            where "orders"."deleted_at" is null 
            group by "orders"."id"
            order by sort0 asc, sort1 desc';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
