<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests\Clauses;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class OrWhereInTest extends TestCase
{
    public function testWhere()
    {
        Order::joinRelations('seller')
            ->whereInJoin('seller.id', [1, 2])
            ->orWhereInJoin('seller.id', [3, 4])
            ->get();

        $queryTest = 'select orders.* 
            from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            where ("sellers"."id" in (?, ?) 
                or 
                "sellers"."id" in (?, ?))
            and "orders"."deleted_at" is null 
            group by "orders"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
