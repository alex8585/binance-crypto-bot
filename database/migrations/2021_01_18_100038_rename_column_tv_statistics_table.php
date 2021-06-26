<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnTvStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        Schema::table('tv_statistics', function (Blueprint $table) {
            $table->renameColumn('tv_technicals_id', 'tv_technical_id');
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
            $table->renameColumn('tv_technical_id', 'tv_technicals_id');
        });
    }
}
