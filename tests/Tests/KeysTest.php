<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Key\Order;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class KeysTest extends TestCase
{
    public function testBelogsTo()
    {
        Order::joinRelations('seller')
            ->get();

        $queryTest = 'select key_orders.* 
            from "key_orders" 
            left join "key_sellers" on "key_sellers"."key_id_seller" = "key_orders"."key_seller_id" 
            group by "key_orders"."key_id_order"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
