<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTvTechnicalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tv_technicals', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->json('oscillators')->nullable();
            $table->json('summary')->nullable();
            $table->json('moving_averages')->nullable();
            $table->string('summary_status')->nullable();
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
        Schema::dropIfExists('tv_technicals');
    }
}
