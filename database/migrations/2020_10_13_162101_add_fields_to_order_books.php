<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToOrderBooks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_books', function (Blueprint $table) {
            $table->float('buy_price');
            $table->float('sell_price');
            $table->float('price_down_border');
            $table->float('bids_volume');
            $table->float('asks_volume');
            $table->string('volume_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_books', function (Blueprint $table) {
            $table->dropColumn('buy_price');
            $table->dropColumn('sell_price');
            $table->dropColumn('price_down_border');
            $table->dropColumn('bids_volume');
            $table->dropColumn('asks_volume');
            $table->dropColumn('volume_status');
        });
    }
}
