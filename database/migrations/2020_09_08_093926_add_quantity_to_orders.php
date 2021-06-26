<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuantityToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->float('quantity', 16, 8)->default(0)->unsigned();
            $table->float('sell_price', 16, 8)->default(0)->unsigned();
            $table->float('order_result', 16, 8)->default(0)->unsigned();
            $table->float('order_result_percent', 16, 8)->default(0)->unsigned();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('quantity');
            $table->dropColumn('sell_price');
            $table->dropColumn('order_result');
            $table->dropColumn('order_result_percent');
        });
    }
}
