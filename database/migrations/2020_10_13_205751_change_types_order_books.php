<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTypesOrderBooks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::table('order_books', function (Blueprint $table) {

            DB::statement('ALTER TABLE order_books MODIFY pierce_price DOUBLE(16,8) UNSIGNED DEFAULT 0;');
            DB::statement('ALTER TABLE order_books MODIFY buy_price DOUBLE(16,8) UNSIGNED DEFAULT 0;');
            DB::statement('ALTER TABLE order_books MODIFY sell_price DOUBLE(16,8) UNSIGNED DEFAULT 0;');
            DB::statement('ALTER TABLE order_books MODIFY price_down_border DOUBLE(16,8) UNSIGNED DEFAULT 0;');
            DB::statement('ALTER TABLE order_books MODIFY bids_volume DOUBLE(16,8) UNSIGNED DEFAULT 0;');
            DB::statement('ALTER TABLE order_books MODIFY asks_volume DOUBLE(16,8) UNSIGNED DEFAULT 0;');
            
        });

      
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()  {
        //
    }
}
