<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests\Relations;

use Fico7489\Laravel\EloquentJoin\Tests\Models\City;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class BelongToManyTest extends TestCase
{
    public function testBelongsToMany()
    {
        City::joinRelations('sellers')->get();

        $queryTest = 'select cities.* from "cities" 
            left join "locations" on "locations"."city_id" = "cities"."id" 
            left join "sellers" on "sellers"."id" = "locations"."seller_id" 
            where "cities"."deleted_at" is null
            group by "cities"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
