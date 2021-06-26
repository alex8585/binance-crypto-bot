<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFieldMaxPercentInTvStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tv_statistics', function (Blueprint $table) {
            $table->float('max_percent', 16, 8)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tv_statistics', function (Blueprint $table) {
            $table->float('max_percent', 16, 8)->default(0)->unsigned()->change();
        });
    }
}
