<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFildsTypesCandidates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candidates', function (Blueprint $table) {
            DB::statement('ALTER TABLE candidates MODIFY pierce_price DOUBLE(16,8) UNSIGNED DEFAULT 0;');
            DB::statement('ALTER TABLE candidates MODIFY yesterday_max_price DOUBLE(16,8) UNSIGNED DEFAULT 0;');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    { 
        Schema::table('candidates', function (Blueprint $table) {
            $table->float('pierce_price')->default(0)->change();
            $table->float('yesterday_max_price')->default(0)->change();
        });
    }
}
