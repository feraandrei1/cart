<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cart_id')->unsigned()->index();
            $table->string('model_type');
            $table->integer('model_id')->unsigned();
            $table->string('name');
            $table->decimal('price', 8, 2);
            $table->decimal('price_with_tax', 8, 2)->nullable();
            $table->decimal('tax', 8, 2)->nullable();
            $table->string('image')->nullable();
            $table->integer('quantity')->unsigned()->nullable();
            $table->string('source')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_items');
    }
}
