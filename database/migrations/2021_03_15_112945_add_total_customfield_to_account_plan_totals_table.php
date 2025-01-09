<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalCustomfieldToAccountPlanTotalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_plan_totals', function (Blueprint $table) {
            $table->integer('total_customfield')->default(0)->after('account_id');
            $table->integer('total_segment')->default(0)->after('account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_plan_totals', function (Blueprint $table) {
           $table->dropColumn('total_customfield');
           $table->dropColumn('total_segment');
        });
    }
}
