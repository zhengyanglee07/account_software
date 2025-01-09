<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionFreeShippingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotion_free_shippings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('applied_countries_type')->default('all');
            $table->json('applied_countries')->nullable();
            $table->string('requirement_type')->nullable();
            $table->decimal('requirement_value')->default(0);
            $table->boolean('is_exclude_shipping_rate')->default(false);
            $table->decimal('exclude_value',9,2)->default(0.00);
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
        Schema::dropIfExists('promotion_free_shippings');
    }
}
