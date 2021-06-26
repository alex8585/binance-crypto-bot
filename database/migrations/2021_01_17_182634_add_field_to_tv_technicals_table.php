<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldToTvTechnicalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tv_technicals', function (Blueprint $table) {
            $table->float('start_price', 16, 8)->default(0)->unsigned();
            $table->integer('last_stat_tf')->default(0)->unsigned();
            
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
            $table->dropColumn('start_price');
            $table->dropColumn('last_stat_tf');
        });
    }
}
