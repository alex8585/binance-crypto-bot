<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatistics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statistics', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->float('buy_price', 16, 8)->default(0)->unsigned();
            $table->float('max15', 16, 8)->default(0)->unsigned();
            $table->float('max30', 16, 8)->default(0)->unsigned();
            $table->float('max60', 16, 8)->default(0)->unsigned();
            $table->float('percent15', 16, 8)->default(0)->unsigned();
            $table->float('percent30', 16, 8)->default(0)->unsigned();
            $table->float('percent60', 16, 8)->default(0)->unsigned();
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
        Schema::dropIfExists('statistics');
    }
}
