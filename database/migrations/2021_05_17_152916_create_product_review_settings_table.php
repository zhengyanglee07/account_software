<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductReviewSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_review_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->string('auto_approve_type')->default('4-stars');
            $table->boolean('discount_type')->default(true);
            $table->foreignId('promotion_id')->nullable()->constrained();
            $table->string('image_option')->default('required');
            $table->string('layout_type')->default('grid');
            $table->boolean('display_review')->default(true);
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
        Schema::dropIfExists('product_review_settings');
    }
}
