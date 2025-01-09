<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalRoleAssignToAccountPlanTotal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_plan_totals', function (Blueprint $table) {
            $table->integer('total_role_assign')->default(0)->after('total_user');
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
            $table->dropColumn('total_role_assign');
        });
    }
}
