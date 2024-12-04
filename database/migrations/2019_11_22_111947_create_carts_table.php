<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cookie');
            $table->integer('auth_user')->unsigned()->nullable();
            $table->decimal('subtotal', 8, 2)->nullable();
            $table->decimal('subtotal_with_tax', 8, 2)->nullable();
            $table->decimal('discount', 8, 2)->nullable();
            $table->decimal('discount_percentage', 8, 2)->nullable();
            $table->decimal('discount_with_tax', 8, 2)->nullable();
            $table->integer('coupon_id')->unsigned()->nullable();
            $table->decimal('shipping_charges', 8, 2)->default(0.00)->nullable();
            $table->decimal('net_total', 8, 2)->nullable();
            $table->decimal('tax', 8, 2)->nullable();
            $table->decimal('total', 8, 2)->nullable();
            $table->decimal('round_off', 8, 2)->nullable();
            $table->decimal('payable', 8, 2)->nullable();
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
        Schema::dropIfExists('carts');
    }
}
