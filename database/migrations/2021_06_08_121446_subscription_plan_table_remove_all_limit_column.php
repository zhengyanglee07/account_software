<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SubscriptionPlanTableRemoveAllLimitColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('max_funnel');
            $table->dropColumn('max_landingpage');
            $table->dropColumn('max_people');
            $table->dropColumn('max_domain');
            $table->dropColumn('max_user');
            $table->dropColumn('max_product');
            $table->dropColumn('max_storage');
            $table->dropColumn('max_form_submission');
            $table->dropColumn('segment_and_tag');
            $table->dropColumn('import');
            $table->dropColumn('export');
            $table->dropColumn('email');
            $table->dropColumn('automation');
            $table->dropColumn('coupons_discount_and_points');
            $table->dropColumn('affiliate');
            $table->dropColumn('price');
            $table->dropColumn('price_id');
            $table->dropColumn('custom_field');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_plans', function (Blueprint $table) {

        });
    }
}
