<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePromotionExtraConditionSelectedCusotmerIdToTargetValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('promotion_extra_conditions', function (Blueprint $table) {
            $table->renameColumn('selected_customer_id','target_value');
            $table->json('selected_customer_id')->change();
            // $table->json('target_value')->nullable()->change();
        });
        // Schema::table('promotion_extra_conditions', function (Blueprint $table) {
        //     // $table->renameColumn('selected_customer_id','target_value');
        //     $table->json('target_value')->nullable()->change();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('promotion_extra_conditions', function (Blueprint $table) {
            $table->renameColumn('target_value','selected_customer_id');
            $table->string('target_value')->change();
        });
        // Schema::table('promotion_extra_conditions', function (Blueprint $table) {
        //     // $table->renameColumn('target_value','selected_customer_id');
        //     $table->string('selected_customer_id')->change();
        // });
    }
}
