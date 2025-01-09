<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCappedAmountToCashbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cashbacks', function (Blueprint $table) {
            $table->integer('capped_amount')->nullable()->after('cashback_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cashbacks', function (Blueprint $table) {
            $table->dropColumn('capped_amount');
        });
    }
}
