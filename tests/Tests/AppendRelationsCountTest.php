<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests\Clauses;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class AppendRelationsCountTest extends TestCase
{
    public function testWhere()
    {
        Order::setAppendRelationsCount(true)->joinRelations('seller.locationPrimary.locationAddressPrimary')->get();

        $queryTest = 'select COUNT(sellers.id) as sellers_count, COUNT(locations.id) as sellers_locations_count, COUNT(location_addresses.id) as sellers_locations_location_addresses_count, "orders".*
            from "orders"
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
