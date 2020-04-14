<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests\Clauses;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class OrWhereRawTest extends TestCase
{
    public function testWhere()
    {
        Order::joinRelations('seller')
            ->whereJoin('seller.id', '=', 1)
            ->orWhereRawJoin('relation:seller.id = ?', [2])
            ->get();

        $queryTest = 'select orders.* 
            from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            where ("sellers"."id" = 1 or "sellers"."id" = 2) 
            and "orders"."deleted_at" is null 
            group by "orders"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
