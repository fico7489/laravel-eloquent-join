<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests\Clauses;

use Fico7489\Laravel\EloquentJoin\EloquentJoinBuilder;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class AggregateJoinTest extends TestCase
{
    public function testAvg()
    {
        Order::relationJoin('seller')
            ->orderByJoin('seller.id', 'asc', EloquentJoinBuilder::AGGREGATE_AVG)
            ->get();

        $queryTest = 'select "orders".*, AVG(sellers.id) as sort 
            from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            where "orders"."deleted_at" is null 
            group by "orders"."id" 
            order by "sellers"."id" asc';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
