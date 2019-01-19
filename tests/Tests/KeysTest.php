<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Key\Order;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Key\Seller;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Key\Location;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class KeysTest extends TestCase
{
    public function testBelongsTo()
    {
        Order::joinRelations('seller')
            ->get();

        $queryTest = 'select key_orders.* 
            from "key_orders" 
            left join "key_sellers" on "key_sellers"."secondary_key" = "key_orders"."secondary_key_seller" 
            group by "key_orders"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testHasOne()
    {
        Location::joinRelations('seller')
            ->get();

        $queryTest = 'select key_locations.* 
            from "key_locations" 
            left join "key_sellers" on "key_sellers"."key_id_location" = "key_locations"."key_id" 
            group by "key_locations"."key_id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testHasMany()
    {
        Seller::joinRelations('orders')
            ->get();

        $queryTest = 'select key_sellers.* 
            from "key_sellers" 
            left join "key_orders" on "key_orders"."secondary_key_seller" = "key_sellers"."secondary_key" 
            group by "key_sellers"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
