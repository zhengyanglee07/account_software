<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLalamoveFulfillmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lalamove_fulfillments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lalamove_quotation_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_detail_id')->constrained()->onDelete('cascade');
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
        Schema::dropIfExists('lalamove_fulfillments');
    }
}
