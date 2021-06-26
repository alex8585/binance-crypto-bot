<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFieldsTypesBalances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('balances', function (Blueprint $table) {
            DB::statement('ALTER TABLE balances MODIFY available DOUBLE(16,8) UNSIGNED DEFAULT 0;');
            DB::statement('ALTER TABLE balances MODIFY on_order DOUBLE(16,8) UNSIGNED DEFAULT 0;');
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('balances', function (Blueprint $table) {
            $table->float('available')->default(0)->change();
            $table->float('on_order')->default(0)->change();
        });
    }
}
