<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\OrderItem;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class ClosureTest extends TestCase
{
    public function testNestOne()
    {
        OrderItem::where(function ($query) {
            $query
                ->orWhereJoin('order.id', '=', 1)
                ->orWhereJoin('order.id', '=', 2);
        })->get();

        $queryTest = 'select "order_items".* 
            from "order_items" 
            left join "orders" on "orders"."id" = "order_items"."order_id" 
            and "orders"."deleted_at" is null 
            where ("orders"."id" = ? or "orders"."id" = ?) 
            and "order_items"."deleted_at" is null';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testNestTwo()
    {
        OrderItem::where(function ($query) {
            $query
                ->orWhereJoin('order.id', '=', 1)
                ->orWhereJoin('order.id', '=', 2)
                ->where(function ($query) {
                    $query->orWhereJoin('order.seller.locationPrimary.id', '=', 3);
                });
        })->get();

        $queryTest = 'select "order_items".* from "order_items" 
            left join "orders" on "orders"."id" = "order_items"."order_id" 
            and "orders"."deleted_at" is null 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            left join "locations" on "locations"."seller_id" = "sellers"."id" 
            and "locations"."is_primary" = ? 
            and "locations"."deleted_at" is null 
            and locations.id =  (
            SELECT id
                FROM locations
                WHERE locations.seller_id = sellers.id
                LIMIT 1
            ) where ("orders"."id" = ? or "orders"."id" = ? 
            and ("locations"."id" = ?)) 
            and "order_items"."deleted_at" is null';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
