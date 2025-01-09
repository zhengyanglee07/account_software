<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UsersProductsTableAddPaymentTypeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_products', function (Blueprint $table) {
           $table->string('payment_type')->default('subscription_and_otp')->after('is_selling');
           $table->string('product_id')->nullable()->after('payment_type');
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
            $table->dropColumn('payment_type');
            $table->dropColumn('product_id');
         });
    }
}
