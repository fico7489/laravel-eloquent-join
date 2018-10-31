<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests\Relations;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class BelongsToTest extends TestCase
{
    public function testBelongsTo()
    {
        Order::joinRelations('seller')->get();

        $queryTest = 'select orders.* from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            where "orders"."deleted_at" is null 
            group by "orders"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testBelongsToHasOne()
    {
        Order::joinRelations('seller.locationPrimary')->get();

        $queryTest = 'select orders.* from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            left join "locations" on "locations"."seller_id" = "sellers"."id" 
            and "locations"."is_primary" = ? 
            and "locations"."deleted_at" is null 
            where "orders"."deleted_at" is null
            group by "orders"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testBelongsToHasMany()
    {
        Order::joinRelations('seller.locations')->get();

        $queryTest = 'select orders.* from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            left join "locations" on "locations"."seller_id" = "sellers"."id" 
            and "locations"."deleted_at" is null 
            where "orders"."deleted_at" is null 
            group by "orders"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testBelongsToHasOneHasMany()
    {
        Order::joinRelations('seller.locationPrimary.integrations')->get();

        $queryTest = 'select orders.* from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            left join "locations" on "locations"."seller_id" = "sellers"."id" 
            and "locations"."is_primary" = ? and "locations"."deleted_at" is null 
            left join "integrations" on "integrations"."location_id" = "locations"."id" 
            and "integrations"."deleted_at" is null 
            where "orders"."deleted_at" is null 
            group by "orders"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testBelongsToHasManyHasOne()
    {
        Order::joinRelations('seller.locationPrimary.locationAddressPrimary')->get();

        $queryTest = 'select orders.* from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            left join "locations" on "locations"."seller_id" = "sellers"."id" 
            and "locations"."is_primary" = ? 
            and "locations"."deleted_at" is null 
            left join "location_addresses" on "location_addresses"."location_id" = "locations"."id" 
            and "location_addresses"."is_primary" = ? 
            and "location_addresses"."deleted_at" is null 
            where "orders"."deleted_at" is null 
            group by "orders"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
