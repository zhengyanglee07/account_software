<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFunnelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('funnel_page_main', function (Blueprint $table) {
            $table->bigIncrements('id');
            // $table->integer('account_id')->unsigned();
            // $table->foreign('account_id')->references('id')->on('accounts');
            $table->bigInteger('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->string('funnel_name');
            $table->string('domain_name')->nullable();
            // $table->foreign('domain_name')->references('accountRandomId')->on('accounts')->onDelete('cascade');
            $table->string('tracking_header')->nullable();
            $table->string('tracking_body')->nullable();
            $table->integer('font_size')->nullable();
            $table->text('text_color')->nullable();
            $table->text('text_family')->nullable();
            $table->string('reference_key')->nullable();
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
        Schema::dropIfExists('funnels');
    }
}
