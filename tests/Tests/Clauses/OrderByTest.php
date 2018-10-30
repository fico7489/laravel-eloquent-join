<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests\Clauses;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class OrderByTest extends TestCase
{
    public function testWhere()
    {
        Order::relationJoin('seller')
            ->orderByJoin('seller.id', 'ASC')
            ->get();

        $queryTest = 'select "orders".*, MAX(sellers.id) as sort
            from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            where "orders"."deleted_at" is null 
            group by "orders"."id"
            order by "sellers"."id" asc';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
