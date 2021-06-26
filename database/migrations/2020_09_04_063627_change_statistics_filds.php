<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeStatisticsFilds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statistics', function (Blueprint $table) {
            $table->float('percent15', 16, 8)->default(0)->change();
            $table->float('percent30', 16, 8)->default(0)->change();
            $table->float('percent60', 16, 8)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statistics', function (Blueprint $table) {
            $table->float('percent15', 16, 8)->default(0)->unsigned()->change();
            $table->float('percent30', 16, 8)->default(0)->unsigned()->change();
            $table->float('percent60', 16, 8)->default(0)->unsigned()->change();
        });
    }
}
