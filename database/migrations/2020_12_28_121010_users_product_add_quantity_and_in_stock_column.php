<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UsersProductAddQuantityAndInStockColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_products', function (Blueprint $table) {
            $table->string('SKU')->after('weight')->nullable();
            $table->integer('quantity')->after('SKU')->default(0);
            $table->boolean('is_selling')->after('quantity')->default(false);
            $table->longText('productDescription')->nullable()->change();
            $table->json('productImageCollection')->nullable()->after('productImagePath');
            $table->string('status')->default('draft')->after('productImageCollection');
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
            $table->dropColumn('SKU');
            $table->dropColumn('quantity');
            $table->dropColumn('is_selling');
            $table->string('productDescription')->nullable()->change();
            $table->dropColumn('productImageCollection');
            $table->dropColumn('status');
        });
    }
}
