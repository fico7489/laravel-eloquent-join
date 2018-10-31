<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests\Relations;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Seller;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class HasManyTest extends TestCase
{
    public function testHasMany()
    {
        Seller::joinRelations('locations')->get();

        $queryTest = 'select sellers.* 
            from "sellers" 
            left join "locations" on "locations"."seller_id" = "sellers"."id" 
            and "locations"."deleted_at" is null 
            group by "sellers"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testHasManyHasOne()
    {
        Seller::joinRelations('locations.city')->get();

        $queryTest = 'select sellers.* 
            from "sellers" left join "locations" on "locations"."seller_id" = "sellers"."id" 
            and "locations"."deleted_at" is null 
            left join "cities" on "cities"."id" = "locations"."city_id" 
            and "cities"."deleted_at" is null 
            group by "sellers"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testHasManyBelongsTo()
    {
        Seller::joinRelations('locations.integrations')->get();

        $queryTest = 'select sellers.* 
            from "sellers" 
            left join "locations" on "locations"."seller_id" = "sellers"."id" 
            and "locations"."deleted_at" is null 
            left join "integrations" on "integrations"."location_id" = "locations"."id"
            and "integrations"."deleted_at" is null 
            group by "sellers"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
