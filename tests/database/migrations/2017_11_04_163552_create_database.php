<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateDatabase.
 */
class CreateDatabase extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('states', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->unsignedInteger('state_id')->nullable();

            $table->foreign('state_id')->references('id')->on('states');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sellers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->unsignedInteger('city_id')->nullable();

            $table->foreign('city_id')->references('id')->on('cities');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number')->nullable();
            $table->unsignedInteger('seller_id')->nullable();

            $table->foreign('seller_id')->references('id')->on('sellers');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('order_id')->nullable();

            $table->foreign('order_id')->references('id')->on('orders');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('address')->nullable();
            $table->boolean('is_primary')->default(0);
            $table->boolean('is_secondary')->default(0);
            $table->unsignedInteger('seller_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();

            $table->foreign('seller_id')->references('id')->on('sellers');
            $table->foreign('city_id')->references('id')->on('cities');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('zip_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->boolean('is_primary')->default(0);
            $table->unsignedInteger('city_id')->nullable();

            $table->foreign('city_id')->references('id')->on('cities');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('location_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->boolean('is_primary')->default(0);
            $table->unsignedInteger('location_id')->nullable();

            $table->foreign('location_id')->references('id')->on('locations');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->unsignedInteger('location_address_id')->nullable();

            $table->foreign('location_address_id')->references('id')->on('location_addresses');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('integrations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->unsignedInteger('location_id')->nullable();

            $table->foreign('location_id')->references('id')->on('locations');

            $table->timestamps();
            $table->softDeletes();
        });

        //for key tests
        Schema::create('key_orders', function (Blueprint $table) {
            $table->increments('id_order_primary');
            $table->unsignedInteger('id_order_owner')->nullable();

            $table->string('number')->nullable();

            $table->unsignedInteger('id_seller_foreign')->nullable();
            $table->foreign('id_seller_foreign')->references('id')->on('sellers');
        });

        Schema::create('key_sellers', function (Blueprint $table) {
            $table->increments('id_seller_primary');
            $table->unsignedInteger('id_seller_owner')->nullable();

            $table->string('title')->nullable();
        });

        Schema::create('key_locations', function (Blueprint $table) {
            $table->increments('id_location_primary');
            $table->unsignedInteger('id_location_owner')->nullable();

            $table->string('address')->nullable();

            $table->unsignedInteger('id_seller_foreign')->nullable();
            $table->foreign('id_seller_foreign')->references('id')->on('sellers');
        });
    }

    /**
     * Reverse the migrations.
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
        Schema::drop('integrations');
        Schema::drop('orders');

        //for key tests
        Schema::drop('key_orders');
        Schema::drop('key_sellers');
        Schema::drop('key_locations');
    }
}
