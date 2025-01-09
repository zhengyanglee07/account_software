<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('users_products')->onDelete('cascade');
            $table->foreignId('account_id')->constrained();
            $table->foreignId('ecommerce_account_id')->constrained();
            $table->string('status')->default('pending');
            $table->integer('star_rating');
            $table->string('name');
            $table->longText('review')->nullable();
            $table->json('image_collection')->nullable();
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
        Schema::dropIfExists('product_reviews');
    }
}
