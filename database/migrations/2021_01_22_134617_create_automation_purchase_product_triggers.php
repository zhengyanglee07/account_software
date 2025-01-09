<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationPurchaseProductTriggers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation_purchase_product_triggers', function (Blueprint $table) {
            $table->id();

            // MySQL will complaint that identifier name too long, use our own fk here
            $table->bigInteger('automation_trigger_id')->unsigned();
            $table->foreign('automation_trigger_id', 'automation_pp_triggers_automation_trigger_id_foreign')->references('id')->on('automation_trigger')->onDelete('cascade');

            $table->bigInteger('users_product_id')->unsigned()->nullable();
            $table->foreign('users_product_id')->references('id')->on('users_products')->onDelete('set null');

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
        Schema::dropIfExists('automation_purchase_product_triggers');
    }
}
