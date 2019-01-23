<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests\Clauses;

use Fico7489\Laravel\EloquentJoin\EloquentJoinBuilder;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class AggregateJoinTest extends TestCase
{
    private $queryTest = 'select orders.*, SUM(sellers.id) as sort0 
            from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            where "orders"."deleted_at" is null 
            group by "orders"."id" 
            order by sort0 asc';

    public function testAvg()
    {
        Order::joinRelations('seller')
            ->orderByJoin('seller.id', 'asc', EloquentJoinBuilder::AGGREGATE_SUM)
            ->get();

        $queryTest = str_replace(EloquentJoinBuilder::AGGREGATE_SUM, EloquentJoinBuilder::AGGREGATE_SUM, $this->queryTest);
        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testSum()
    {
        Order::joinRelations('seller')
            ->orderByJoin('seller.id', 'asc', EloquentJoinBuilder::AGGREGATE_AVG)
            ->get();

        $queryTest = str_replace(EloquentJoinBuilder::AGGREGATE_SUM, EloquentJoinBuilder::AGGREGATE_AVG, $this->queryTest);
        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testMax()
    {
        Order::joinRelations('seller')
            ->orderByJoin('seller.id', 'asc', EloquentJoinBuilder::AGGREGATE_MAX)
            ->get();

        $queryTest = str_replace(EloquentJoinBuilder::AGGREGATE_SUM, EloquentJoinBuilder::AGGREGATE_MAX, $this->queryTest);
        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testMin()
    {
        Order::joinRelations('seller')
            ->orderByJoin('seller.id', 'asc', EloquentJoinBuilder::AGGREGATE_MIN)
            ->get();

        $queryTest = str_replace(EloquentJoinBuilder::AGGREGATE_SUM, EloquentJoinBuilder::AGGREGATE_MIN, $this->queryTest);
        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testCount()
    {
        Order::joinRelations('seller')
            ->orderByJoin('seller.id', 'asc', EloquentJoinBuilder::AGGREGATE_COUNT)
            ->get();

        $queryTest = str_replace(EloquentJoinBuilder::AGGREGATE_SUM, EloquentJoinBuilder::AGGREGATE_COUNT, $this->queryTest);
        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
