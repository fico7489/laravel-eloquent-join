<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Key\Order;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Key\Seller;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class KeysSecondaryTest extends TestCase
{
    public function testBelogsToSecondary()
    {
        Order::joinRelations('sellerSecondary')
            ->get();

        $queryTest = 'select key_orders.* 
            from "key_orders" 
            left join "key_sellers" on "key_sellers"."key_id_seller_secondary" = "key_orders"."key_seller_id" 
            group by "key_orders"."key_id_order"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    /*public function testHasOneSecondary()
    {
        Seller::joinRelations('locationSecondary')
            ->get();

        $queryTest = 'select key_sellers.*
            from "key_sellers"
            left join "key_locations" on "key_locations"."key_seller_id" = "key_sellers"."key_id_seller_secondary"
            group by "key_sellers"."key_id_seller"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testHasManySecondary()
    {
        Seller::joinRelations('locationsSecondary')
            ->get();

        $queryTest = 'select key_sellers.*
            from "key_sellers"
            left join "key_locations" on "key_locations"."key_seller_id" = "key_sellers"."key_id_seller_secondary"
            group by "key_sellers"."key_id_seller"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }*/
}
