<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class BelongsToTest extends TestCase
{
    public function testBelongsTo()
    {
        Order::relationJoin('seller')->get();

        $queryTest = 'select "orders".* from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            where "orders"."deleted_at" is null 
            group by "orders"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testBelongsToHasOne()
    {
        Order::relationJoin('seller.locationPrimary')->get();

        $queryTest = 'select "orders".* from "orders" 
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
        //
    }

    public function testBelongsToHasOneHasMany()
    {
        //
    }

    public function testBelongsToHasManyHasOne()
    {
        //
    }
}
