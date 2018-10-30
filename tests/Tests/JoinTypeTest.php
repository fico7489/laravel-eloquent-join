<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Seller;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class JoinTypeTest extends TestCase
{
    public function testLeftJoin()
    {
        Seller::setLeftJoin(true)->whereJoin('city.name', '=', 'test')->get();

        $queryTest = 'select "sellers".*
            from "sellers" 
            left join "cities" 
            on "cities"."id" = "sellers"."city_id" 
            and "cities"."deleted_at" is null 
            where "cities"."name" = ?';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testInnerJoin()
    {
        Seller::setLeftJoin(false)->whereJoin('city.name', '=', 'test')->get();

        $queryTest = 'select "sellers".*
            from "sellers" 
            inner join "cities" 
            on "cities"."id" = "sellers"."city_id" 
            and "cities"."deleted_at" is null 
            where "cities"."name" = ?';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
