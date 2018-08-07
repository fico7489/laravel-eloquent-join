<?php

namespace Fico7489\Laravel\EloquentJoin\Tests;

use Fico7489\Laravel\EloquentJoin\Tests\Models\Seller;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Order;
use Fico7489\Laravel\EloquentJoin\Tests\Models\OrderItem;
use Fico7489\Laravel\EloquentJoin\Tests\Models\Location;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp()
    {
        parent::setUp();

        $seller = Seller::create(['title' => 1]);
        $seller2 = Seller::create(['title' => 2]);
        $seller3 = Seller::create(['title' => 3]);
        Seller::create(['title' => 4]);

        Location::create(['address' => 1, 'seller_id' => $seller->id]);
        Location::create(['address' => 2, 'seller_id' => $seller2->id]);
        Location::create(['address' => 3, 'seller_id' => $seller3->id]);
        Location::create(['address' => 3, 'seller_id' => $seller3->id]);

        Location::create(['address' => 4, 'seller_id' => $seller3->id, 'is_primary' => 1]);
        Location::create(['address' => 5, 'seller_id' => $seller3->id, 'is_secondary' => 1]);

        Order::create(['number' => '1', 'seller_id' => $seller->id]);
        Order::create(['number' => '2', 'seller_id' => $seller2->id]);
        Order::create(['number' => '3', 'seller_id' => $seller3->id]);

        OrderItem::create(['name' => '1', 'order_id' => $seller->id]);
        OrderItem::create(['name' => '2', 'order_id' => $seller2->id]);
        OrderItem::create(['name' => '3', 'order_id' => $seller3->id]);

        $this->startListening();
    }

    protected function startListening()
    {
        \DB::enableQueryLog();
    }

    protected function fetchQuery()
    {
        $log = \DB::getQueryLog();

        return end($log)['query'];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
