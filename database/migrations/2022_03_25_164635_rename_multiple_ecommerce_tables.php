<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameMultipleEcommerceTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_page_views', function (Blueprint $table) {
            $table->renameColumn('name', 'value');
            $table->dropColumn(['duration', 'visited_at', 'existed_at']);
        });
        Schema::rename('ecommerce_page_views', 'ecommerce_tracking_logs');
        Schema::table('ecommerce_visitors', function (Blueprint $table) {
            $table->renameColumn('is_complete', 'is_completed');
        });
        Schema::rename('product_unique_page_views', 'ecommerce_product_unique_page_views');
        Schema::table('ecommerce_abandoned_carts', function (Blueprint $table) {
            $table->renameColumn('view_at', 'abandoned_at');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('ecommerce_tracking_logs', 'ecommerce_page_views');
        Schema::table('ecommerce_page_views', function (Blueprint $table) {
            $table->dateTime('existed_at')->nullable()->after('value');
            $table->dateTime('visited_at')->nullable()->after('value');
            $table->integer('duration')->nullable()->after('value');
            $table->renameColumn('value', 'name');
            
            
        });
        Schema::table('ecommerce_visitors', function (Blueprint $table) {
            $table->renameColumn('is_completed', 'is_complete');
        });
        Schema::rename('ecommerce_product_unique_page_views', 'product_unique_page_views');
        Schema::table('ecommerce_abandoned_carts', function (Blueprint $table) {
            $table->renameColumn( 'abandoned_at', 'view_at');
        });
    }
}