<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Seller;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class JoinTypeTest extends TestCase
{
    public function testLeftJoin()
    {
        Seller::setLeftJoin(true)->whereJoin('city.name', '=', 'test')->get();

        $queryTest = 'select sellers.*
            from "sellers"
            left join "cities"
            on "cities"."id" = "sellers"."city_id"
            and "cities"."deleted_at" is null
            where "cities"."name" = test';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testInnerJoin()
    {
        Seller::setLeftJoin(false)->whereJoin('city.name', '=', 'test')->get();

        $queryTest = 'select sellers.*
            from "sellers"
            inner join "cities"
            on "cities"."id" = "sellers"."city_id"
            and "cities"."deleted_at" is null
            where "cities"."name" = test';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testMixedJoin()
    {
        Order::joinRelations('seller', true)->joinRelations('seller.city', false)->joinRelations('seller.city.state', true)->get();

        $queryTest = 'select orders.* 
            from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            inner join "cities" on "cities"."id" = "sellers"."city_id" 
            and "cities"."deleted_at" is null 
            left join "states" on "states"."id" = "cities"."state_id" 
            and "states"."deleted_at" is null 
            where "orders"."deleted_at" is null 
            group by "orders"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
