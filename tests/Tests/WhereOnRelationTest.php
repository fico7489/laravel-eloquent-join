<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Seller;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class WhereOnRelationTest extends TestCase
{
    public function testWhereOnRelationWithOrderByJoin()
    {
        //location have two where  ['is_primary => 0', 'is_secondary' => 0]
        $items = Seller::orderByJoin('location.id', 'desc')
            ->get()
        ;
        //echo $items->toSql();exit;
        $queryTest = '/select "sellers".* from "sellers" left join "locations" on "locations"."seller_id" = "sellers"."id" and "locations"."is_primary" = \? and "locations"."is_secondary" = \? and "locations"."deleted_at" is null group by "sellers"."id" order by "locations"."id" desc/';
        $this->assertRegExp($queryTest, $this->fetchQuery());
        //$this->assertEquals(1, 1);

    }

}
