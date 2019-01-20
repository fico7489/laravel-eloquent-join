<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Key\Order;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Key\Seller;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class KeysOwnerTest extends TestCase
{
    public function testBelogsTo()
    {
        Order::joinRelations('sellerOwner')
            ->get();

        $queryTest = 'select key_orders.* 
            from "key_orders" 
            left join "key_sellers" on "key_sellers"."id_seller_owner" = "key_orders"."id_seller_foreign" 
            group by "key_orders"."id_order_primary"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testHasOne()
    {
        Seller::joinRelations('locationOwner')
            ->get();

        $queryTest = 'select key_sellers.* 
            from "key_sellers" 
            left join "key_locations" on "key_locations"."id_seller_foreign" = "key_sellers"."id_seller_owner" 
            group by "key_sellers"."id_seller_primary"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testHasMany()
    {
        Seller::joinRelations('locationsOwner')
            ->get();

        $queryTest = 'select key_sellers.* 
            from "key_sellers" 
            left join "key_locations" on "key_locations"."id_seller_foreign" = "key_sellers"."id_seller_owner" 
            group by "key_sellers"."id_seller_primary"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
