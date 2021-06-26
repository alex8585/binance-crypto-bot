<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFildsTypesOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            DB::statement('ALTER TABLE orders MODIFY buy_time  TIMESTAMP DEFAULT NULL;');
            DB::statement('ALTER TABLE orders MODIFY sell_time  TIMESTAMP DEFAULT NULL;');
            
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
            $table->timestamp('buy_time')->useCurrent()->change();
            $table->timestamp('sell_time')->useCurrent()->change();
        });
    }
}
