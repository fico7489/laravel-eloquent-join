<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateDatabase
 */
class CreateDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('order_id')->unsigned()->index()->nullable();

            $table->foreign('order_id')->references('id')->on('orders')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number')->nullable();
            $table->integer('seller_id')->unsigned()->index()->nullable();

            $table->foreign('seller_id')->references('id')->on('sellers')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('sellers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();

            $table->integer('city_id')->unsigned()->index()->nullable();

            $table->foreign('city_id')->references('id')->on('cities')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('locations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('address')->nullable();
            $table->boolean('is_primary')->default(0);
            $table->boolean('is_secondary')->default(0);
            $table->integer('seller_id')->unsigned()->index()->nullable();

            $table->foreign('seller_id')->references('id')->on('sellers')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('state_id')->unsigned()->index()->nullable();

            $table->foreign('state_id')->references('id')->on('states')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('zip_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->boolean('is_primary')->default(0);
            $table->integer('city_id')->unsigned()->index()->nullable();

            $table->foreign('city_id')->references('id')->on('cities')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('states', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('location_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->boolean('is_primary')->default(0);
            $table->integer('location_id')->unsigned()->index()->nullable();

            $table->foreign('location_id')->references('id')->on('locations')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
        Schema::drop('sellers');
        Schema::drop('order_items');
        Schema::drop('locations');
        Schema::drop('cities');
        Schema::drop('zip_codes');
        Schema::drop('states');
        Schema::drop('location_addresses');
    }
}
