<?php

namespace Fico7489\Laravel\EloquentJoin\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Seller;

class JoinTest extends TestCase
{
    public function testWhereJoinNoRelation()
    {
        Seller::whereJoin('title', '=', 'test')->get();

        $queryTest = '/select \* from "sellers" where "sellers"."title" = \?/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testWhereJoinBelongsTo()
    {
        Seller::whereJoin('city.name', '=', 'test')->get();

        $queryTest = '/select "sellers".* from "sellers" left join "cities" on "cities"."id" = "sellers"."city_id" where "cities"."deleted_at" is null and "cities"."name" = \? group by "sellers"."id"/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testWhereJoinHasOne()
    {
        Seller::whereJoin('locationPrimary.address', '=', 'test')->get();

        $queryTest = '/select "sellers".* from "sellers" left join "locations" on "locations"."seller_id" = "sellers"."id" where "locations"."deleted_at" is null and "locations"."is_primary" = \? and "locations"."address" = \? group by "sellers"."id"/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testWhereJoinBelongsToBelongsTo()
    {
        Seller::whereJoin('city.state.name', '=', 'test')->get();

        $queryTest = '/select "sellers".* from "sellers" left join "cities" on "cities"."id" = "sellers"."city_id" left join "states" on "states"."id" = "cities"."state_id" where "cities"."deleted_at" is null and "states"."deleted_at" is null and "states"."name" = \? group by "sellers"."id"/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testWhereJoinBelongsToHasOne()
    {
        Seller::whereJoin('city.zipCodePrimary.name', '=', 'test')->get();

        $queryTest = '/select "sellers".* from "sellers" left join "cities" on "cities"."id" = "sellers"."city_id" left join "zip_codes" on "zip_codes"."city_id" = "cities"."id" where "cities"."deleted_at" is null and "zip_codes"."deleted_at" is null and "zip_codes"."is_primary" = \? and "zip_codes"."name" = \? group by "sellers"."id"/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testWhereJoinHasOneHasOne()
    {
        Seller::whereJoin('locationPrimary.locationAddressPrimary.name', '=', 'test')->get();

        $queryTest = '/select "sellers".* from "sellers" left join "locations" on "locations"."seller_id" = "sellers"."id" left join "location_addresses" on "location_addresses"."location_id" = "locations"."id" where "locations"."deleted_at" is null and "locations"."is_primary" = \? and "location_addresses"."deleted_at" is null and "location_addresses"."is_primary" = \? and "location_addresses"."name" = \? group by "sellers"."id"/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }

    public function testWhereJoinHasBelongsTo()
    {
        Seller::whereJoin('locationPrimary.city.name', '=', 'test')->get();

        $queryTest = '/select "sellers".* from "sellers" left join "locations" on "locations"."seller_id" = "sellers"."id" left join "cities" on "cities"."id" = "locations"."city_id" where "locations"."deleted_at" is null and "locations"."is_primary" = \? and "cities"."deleted_at" is null and "cities"."name" = \? group by "sellers"."id"/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
    }
}
