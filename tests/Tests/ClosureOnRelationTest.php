<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Seller;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class ClosureOnRelationTest extends TestCase
{
    public function testWhereOnRelationWithOrderByJoin()
    {
        //location have two where  ['is_primary => 0', 'is_secondary' => 0]
        $items = Seller::orderByJoin('location.id', 'desc')->get();
        $queryTest = 'select sellers.*, MAX("locations"."id") as sort from "sellers" 
            left join "locations" 
            on "locations"."seller_id" = "sellers"."id" 
            and "locations"."is_primary" = ? 
            and "locations"."is_secondary" = ? 
            and "locations"."deleted_at" is null 
            group by "sellers"."id"
            order by sort desc';
        $bindingsTest = [0, 0];

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
        $this->assertEquals($bindingsTest, $this->fetchBindings());

        //locationPrimary have one where ['is_primary => 1']
        $items = Seller::orderByJoin('locationPrimary.id', 'desc')->get();
        $queryTest = 'select sellers.*, MAX("locations"."id") as sort from "sellers" 
            left join "locations" 
            on "locations"."seller_id" = "sellers"."id" 
            and "locations"."is_primary" = ? 
            and "locations"."deleted_at" is null 
            group by "sellers"."id"
            order by sort desc';

        $bindingsTest = [1];
        $this->assertQueryMatches($queryTest, $this->fetchQuery());
        $this->assertEquals($bindingsTest, $this->fetchBindings());

        //locationPrimary have one where ['is_secondary => 1']
        $items = Seller::orderByJoin('locationSecondary.id', 'desc')->get();
        $queryTest = 'select sellers.*, MAX("locations"."id") as sort from "sellers" 
            left join "locations" 
            on "locations"."seller_id" = "sellers"."id" 
            and "locations"."is_secondary" = ? 
            and "locations"."deleted_at" is null 
            group by "sellers"."id"
            order by sort desc';

        $bindingsTest = [1];
        $this->assertQueryMatches($queryTest, $this->fetchQuery());
        $this->assertEquals($bindingsTest, $this->fetchBindings());

        //locationPrimary have one where ['is_primary => 1'] and one orWhere ['is_secondary => 1']
        $items = Seller::orderByJoin('locationPrimaryOrSecondary.id', 'desc')->get();
        $queryTest = 'select sellers.*, MAX("locations"."id") as sort from "sellers" 
            left join "locations" 
            on "locations"."seller_id" = "sellers"."id" 
            and "locations"."is_primary" = ? 
            or "locations"."is_secondary" = ? 
            and "locations"."deleted_at" is null 
            group by "sellers"."id"
            order by sort desc';

        $bindingsTest = [1, 1];
        $this->assertQueryMatches($queryTest, $this->fetchQuery());
        $this->assertEquals($bindingsTest, $this->fetchBindings());
    }

    public function testWhereOnRelationWithoutOrderByJoin()
    {
        $seller = Seller::find(1);

        $seller->locationPrimary;
        $queryTest = 'select * from "locations" 
            where "locations"."seller_id" = ? 
            and "locations"."seller_id" is not null 
            and "is_primary" = ? 
            and "locations"."deleted_at" is null 
            limit 1';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());

        $seller->locationPrimary()->where(['is_secondary' => 1])->get();
        $queryTest = 'select * from "locations" 
            where "locations"."seller_id" = ? 
            and "locations"."seller_id" is not null 
            and "is_primary" = ? 
            and ("is_secondary" = ?)
            and "locations"."deleted_at" is null';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
