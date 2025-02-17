<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAccountPlanTotalTotalImage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_plan_totals', function (Blueprint $table) {
            $table->decimal('total_image_storage',9,2)->change();
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
            $table->integer('total_image_storage')->change();
        });
    }
}
