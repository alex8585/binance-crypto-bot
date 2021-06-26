<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTableTvTechnicals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tv_technicals', function (Blueprint $table) {
            $table->dropColumn('oscillators');
            $table->dropColumn('summary');
            $table->dropColumn('moving_averages');
            $table->dropColumn('summary_status');

            $table->string('timeframe')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('stat_last_time')->nullable()->default(null);
            $table->string('indicator_value')->nullable();
           
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tv_technicals', function (Blueprint $table) {
            $table->json('oscillators')->nullable();
            $table->json('summary')->nullable();
            $table->json('moving_averages')->nullable();
            $table->string('summary_status')->nullable();
        });
    }
}
