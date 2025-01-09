<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->bigInteger('extra_condition_id')->unsigned();
            $table->string('discount_code')->nullable()->unique();
            $table->string('discount_type');
            $table->integer('discount_id')->unsigned();
            $table->string('promotion_category')->nullable();
            $table->string('promotion_method')->nullable();
            $table->string('promotion_name')->nullable();
            $table->string('display_name')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_expiry')->default(false);
            $table->integer('promotion_usage')->default(0);
            $table->string('promotion_status')->default('active');
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
        Schema::dropIfExists('promotions');
    }
}
