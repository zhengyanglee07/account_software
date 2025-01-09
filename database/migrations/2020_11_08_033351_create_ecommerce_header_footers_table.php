<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcommerceHeaderFootersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_header_footers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->string('type')->default('Draft');
            $table->string('active_status')->default('inactive');
            $table->string('sectionType')->nullable();
            $table->string('name')->nullable();
            $table->longText('elementArray')->nullable();
            // $table->string('path')->nullable();
            // $table->integer('duplicated_count')->nullable();
            $table->integer('total_id_count')->nullable();
            // $table->integer('page_padding')->default(950);
            $table->string('pageLayout',191)->default('fullWidth');
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
        Schema::dropIfExists('ecommerce_header_footers');
    }
}
