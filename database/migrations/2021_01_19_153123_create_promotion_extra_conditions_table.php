<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionExtraConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotion_extra_conditions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('target_customer_type')->nullable();
            $table->json('selected_customer_id')->nullable();
            $table->string('store_limit_type')->default('unlimited');
            $table->integer('store_limit_value')->nullable();
            $table->integer('store_usage')->default(0);
            $table->string('customer_limit_type')->default('unlimited');
            $table->integer('customer_limit_value')->default(0)->nullable();
            $table->timestamps();
        });

        Schema::table('promotions',function(Blueprint $table){
            $table->foreign('extra_condition_id')->references('id')->on('promotion_extra_conditions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotion_extra_conditions');
        Schema::table('promotions',function(Blueprint $table){
            $table->dropForeign('extra_condition_id');
        });

    }
}
