<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsTypeAndOrderidToOrderBooks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_books', function (Blueprint $table) {
            $table->integer('order_id')->unsigned()->index()->nullable();
            $table->string('type')->default('candidate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_books', function (Blueprint $table) {
            $table->dropColumn('order_id');
            $table->dropColumn('type');
        });
    }
}
