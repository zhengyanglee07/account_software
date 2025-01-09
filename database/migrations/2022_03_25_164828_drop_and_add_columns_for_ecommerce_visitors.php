<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropAndAddColumnsForEcommerceVisitors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_visitors', function (Blueprint $table) {
            $table->dropColumn(['order_id', 'email']);
        });
        Schema::table('ecommerce_visitors', function (Blueprint $table) {
            $table->bigInteger('order_id')->unsigned()->nullable()->after('account_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->bigInteger('processed_contact_id')->unsigned()->nullable()->after('account_id');
            $table->foreign('processed_contact_id')->references('id')->on('processed_contacts')->onDelete('cascade');
            $table->string('sales_channel')->nullable()->after('is_completed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        
        Schema::table('ecommerce_visitors', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });
        Schema::table('ecommerce_visitors', function (Blueprint $table) {
            $table->dropForeign(['processed_contact_id']);
        });
        Schema::table('ecommerce_visitors', function (Blueprint $table) {
            $table->dropColumn(['order_id', 'processed_contact_id', 'sales_channel']);
        });
        
        Schema::table('ecommerce_visitors', function (Blueprint $table) {
            $table->integer('order_id')->nullable()->after('account_id');
            $table->string('email')->nullable()->after('account_id');
        });
        Schema::enableForeignKeyConstraints();
    }
}
