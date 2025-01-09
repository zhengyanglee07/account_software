<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductUniquePageViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_unique_page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->bigInteger('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('users_products')->onDelete('cascade');
            $table->integer('view_count')->default(1);
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
        Schema::dropIfExists('product_unique_page_views');
    }
}
