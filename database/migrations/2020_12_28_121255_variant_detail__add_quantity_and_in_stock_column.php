<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VariantDetailAddQuantityAndInStockColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('variant_details', function (Blueprint $table) {
            $table->integer('quantity')->after('weight')->default(0);
            $table->boolean('is_selling')->after('quantity')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('variant_details', function (Blueprint $table) {
            $table->dropColumn('quantity');
            $table->dropColumn('is_selling');
        });
    }
}
