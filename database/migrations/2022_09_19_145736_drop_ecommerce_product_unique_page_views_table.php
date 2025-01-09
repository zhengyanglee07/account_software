<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropEcommerceProductUniquePageViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('ecommerce_product_unique_page_views');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('ecommerce_product_unique_page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_product_id')->nullable()->constrained();
            $table->timestamps();
        });
    }
}
