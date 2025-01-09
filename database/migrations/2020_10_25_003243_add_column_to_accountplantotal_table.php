<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToAccountplantotalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_plan_totals', function (Blueprint $table) {
            $table->integer('total_funnel')->default(0)->change();
            $table->integer('total_landingpage')->default(0)->change();
            $table->integer('total_domain')->default(0)->change();
            $table->integer('total_people')->default(0)->change();
            
            $table->integer('total_email')->default(0)->after('total_user');
            $table->integer('total_form')->default(0)->after('total_user');
            $table->integer('total_image_storage')->default(0)->after('total_user');
            $table->integer('total_product')->default(0)->after('total_user');
            $table->integer('total_automation')->default(0)->after('total_user');




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
            $table->dropColumn('total_email');
            $table->dropColumn('total_form');
            $table->dropColumn('total_image_storage');
            $table->dropColumn('total_product');
            $table->dropColumn('total_automation');
        });
    }
}
