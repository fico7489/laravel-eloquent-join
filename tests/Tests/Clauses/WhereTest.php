<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Tests\Clauses;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\TestCase;

class WhereTest extends TestCase
{
    public function testWhere()
    {
        Order::joinRelations('seller')
            ->whereJoin('seller.id', '=', 1)
            ->get();

        $queryTest = 'select orders.* 
            from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            where "sellers"."id" = ? 
            and "orders"."deleted_at" is null 
            group by "orders"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testMultipleWhere()
    {
        Order::whereJoin('seller.id', '=', 1)
            ->whereJoin('seller.title', '=', 'shop')
            ->whereJoin('seller.locations.city_id', '=', 8)
            ->get();

        $queryTest = 'select orders.* 
            from "orders" 
            left join "sellers" on "sellers"."id" = "orders"."seller_id" 
            left join "locations" on "locations"."seller_id" = "sellers"."id" and "locations"."deleted_at" is null
            where "sellers"."id" = ? 
            and "sellers"."title" = ? 
            and "locations"."city_id" = ? 
            and "orders"."deleted_at" is null 
            group by "orders"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }

    public function testMultipleWhereAlias()
    {
        $builder = Order::setUseTableAlias(true)
            ->whereJoin('seller.id', '=', 1)
            ->whereJoin('seller.title', '=', 'shop')
            ->whereJoin('seller.locations.city_id', '=', 8);

        $builder->get();

        [$sellerAlias, $locationAlias] = array_values($this->getBuilderJoinedTables($builder));

        $queryTest = 'select orders.* 
            from "orders" 
            left join "sellers" as "'.$sellerAlias.'" 
                on "'.$sellerAlias.'"."id" = "orders"."seller_id" 
            left join "locations" as "'.$locationAlias.'" 
                on "'.$locationAlias.'"."seller_id" = "'.$sellerAlias.'"."id" 
                and "'.$locationAlias.'"."deleted_at" is null
            where "'.$sellerAlias.'"."id" = ? 
            and "'.$sellerAlias.'"."title" = ? 
            and "'.$locationAlias.'"."city_id" = ?
            and "orders"."deleted_at" is null 
            group by "orders"."id"';

        $this->assertQueryMatches($queryTest, $this->fetchQuery());
    }
}
