<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFieldsTypesOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            DB::statement('ALTER TABLE orders MODIFY pierce_price DOUBLE(16,8) UNSIGNED DEFAULT 0;');
            DB::statement('ALTER TABLE orders MODIFY buy_price DOUBLE(16,8) UNSIGNED DEFAULT 0;');
            DB::statement('ALTER TABLE orders MODIFY stop_price1 DOUBLE(16,8) UNSIGNED DEFAULT 0;');
            DB::statement('ALTER TABLE orders MODIFY stop_price2 DOUBLE(16,8) UNSIGNED DEFAULT 0;');
           
            // $table->float('pierce_price', 16, 8)->default(0)->change();
            // $table->float('buy_price', 16, 8)->default(0)->change();
            // $table->float('stop_price1', 16, 8)->default(0)->change();
            // $table->float('stop_price2', 16, 8)->default(0)->change();
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
            $table->float('pierce_price')->default(0)->change();
            $table->float('buy_price')->default(0)->change();
            $table->float('stop_price1')->default(0)->change();
            $table->float('stop_price2')->default(0)->change();
        });
    }
}
