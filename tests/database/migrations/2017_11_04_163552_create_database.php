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
        Schema::create('order_item', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name');
			$table->integer('order_id')->unsigned()->index();

			$table->foreign('order_id')->references('id')->on('orders')
				->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orders', function (Blueprint $table) {
			$table->increments('id');
			$table->string('number')->nullable();
			$table->integer('seller_id')->unsigned()->index();

			$table->foreign('seller_id')->references('id')->on('sellers')
				->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
		
		Schema::create('sellers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
		
		Schema::create('locations', function (Blueprint $table) {
			$table->increments('id');
			$table->string('address')->nullable();
			$table->integer('seller_id')->unsigned()->index();

			$table->foreign('seller_id')->references('id')->on('sellers')
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
    }
}