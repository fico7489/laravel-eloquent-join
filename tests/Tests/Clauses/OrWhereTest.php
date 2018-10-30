<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests\Clauses;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class OrWhereTest extends TestCase
{
    public function testWhere()
    {
        Order::relationJoin('seller')
            ->whereJoin('seller.id', '=', 1)
            ->orWhereJoin('seller.id', '=', 2)
            ->get();

        $queryTest = 'select "orders".* 
            from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            where ("sellers"."id" = ? or "sellers"."id" = ?) 
            and "orders"."deleted_at" is null 
            group by "orders"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
