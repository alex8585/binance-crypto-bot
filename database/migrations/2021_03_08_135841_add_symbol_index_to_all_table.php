<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSymbolIndexToAllTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('options', function (Blueprint $table) {
            $table->index('key');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->index('max10minutes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('options', function (Blueprint $table) {
            $table->dropIndex(['key']);
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropIndex(['max10minutes']);
        });
    }
}
