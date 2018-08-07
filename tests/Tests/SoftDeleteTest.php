<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\OrderItem;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class SoftDeleteTest extends TestCase
{
    public function testRelatedOnlyTrashedOnRelation()
    {
        OrderItem::orderByJoin('orderOnlyTrashed.number')->get();
        $queryTest = '/select "order_items".* from "order_items" left join "orders" on "orders"."id" = "order_items"."order_id" and "orders"."deleted_at" is not null where "order_items"."deleted_at" is null group by "order_items"."id" order by "orders"."number" asc/';
        //$this->assertRegExp($queryTest, $this->fetchQuery());
    }
}
