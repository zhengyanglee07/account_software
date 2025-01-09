<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLalamoveQuotationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lalamove_quotations', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('order_id')->unsigned();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');

            $table->string('scheduled_at')->nullable();
            $table->string('service_type');
            $table->longText('stops');
            $table->longText('deliveries');
            $table->longText('requester_contacts');
            $table->longText('special_requests');

            $table->string('total_fee_amount')->nullable();
            $table->string('total_fee_currency')->nullable();

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
        Schema::dropIfExists('lalamove_quotations');
    }
}
