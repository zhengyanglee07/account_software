<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SubscriptionPlansAddColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
           $table->integer('max_product')->nullable()->after('max_user');
           $table->integer('max_storage')->nullable()->after('max_product');
           $table->integer('max_form_submission')->nullable()->after('max_storage');
           $table->string('segment_and_tag')->nullable()->after('max_form_submission');
           $table->string('import')->nullable()->after('segment_and_tag');
           $table->string('export')->nullable()->after('import');
           $table->integer('email')->nullable()->after('export');
           $table->integer('automation')->nullable()->after('email');
           $table->string('coupons_discount_and_points')->nullable()->after('automation');
           $table->integer('affiliate')->nullable()->after('coupons_discount_and_points');
           $table->string('price_id')->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
