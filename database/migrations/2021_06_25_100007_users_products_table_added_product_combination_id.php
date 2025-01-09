<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UsersProductsTableAddedProductCombinationId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_products', function (Blueprint $table) {
            $table->string('product_combination_id')->nullable()->after('reference_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_products', function (Blueprint $table) {
            $table->dropColumn('product_combination_id');
        });
    }
}
