<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->float('pierce_price');
            $table->float('buy_price');
            $table->timestamp('buy_time');
            $table->integer('binance_id')->unsigned()->index();
            $table->integer('circle_id')->unsigned()->index();
            $table->integer('candidate_id')->unsigned()->index();
            $table->string('status');
            $table->string("client_orde_id");
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
        Schema::dropIfExists('orders');
    }
}
