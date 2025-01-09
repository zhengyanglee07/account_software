<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropAccountIdFromEcommerceProductUniquePageViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        
        Schema::table('product_unique_page_views', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
        });
        Schema::table('product_unique_page_views', function (Blueprint $table) {
            $table->dropColumn(['account_id', 'view_count']);
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        
        Schema::table('product_unique_page_views', function (Blueprint $table) {
            $table->foreignId('account_id')->constrained()->after('id');
            $table->integer('view_count')->default(1)->after('product_id');
        });
        Schema::enableForeignKeyConstraints();
    }
}