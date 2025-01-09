<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OrderDetailAddVariantAndCustomizationColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->json('variant')->after('product_name')->nullable();
            $table->json('customization')->after('variant')->nullable();
            $table->string('image_url')->after('customization')->nullable();

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('variant');
            $table->dropColumn('customization');
            $table->dropColumn('image_url');

        });


    }
}
