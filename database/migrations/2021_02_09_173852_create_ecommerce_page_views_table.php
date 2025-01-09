<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEcommercePageViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_page_views', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('visitor_id')->unsigned();
            $table->foreign('visitor_id')->references('id')->on('ecommerce_visitors')->onDelete('cascade');
            $table->string('reference_key');
            $table->string('type')->default('page');
            $table->string('name');
            $table->integer('duration')->nullable();
            $table->dateTime('visited_at');
            $table->dateTime('existed_at')->nullable();
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
        Schema::dropIfExists('ecommerce_page_views');
    }
}
