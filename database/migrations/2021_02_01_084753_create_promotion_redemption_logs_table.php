<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionRedemptionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotion_redemption_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('promotion_id')->nullable();
            $table->string('discount_code')->nullable();
            $table->string('customer_email')->nullable();
            $table->bigInteger('customer_id')->nullable();
            $table->integer('applied_usage');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotion_redemption_logs');
    }
}
