<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTvStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tv_statistics', function (Blueprint $table) {
            $table->id();
            $table->integer('tv_technicals_id')->unsigned()->index();
            $table->string('symbol');
            $table->float('buy_price', 16, 8)->default(0)->unsigned();
            $table->float('max', 16, 8)->default(0)->unsigned();
            $table->float('max_percent', 16, 8)->default(0)->unsigned();
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
        Schema::dropIfExists('tv_statistics');
    }
}
