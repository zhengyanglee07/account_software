<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EcommercePreferenceTableRemoveIsMobilenumberColumnAddCheckoutMethodColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_preferences', function (Blueprint $table) {
            $table->String('checkout_method')->default('email address')->after('is_companyname');
            $table->String('register_methods')->default('email address')->after('checkout_method');
            $table->dropColumn('is_mobilenumber');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecommerce_preferences', function (Blueprint $table) {
            $table->dropColumn('checkout_method');
            $table->dropColumn('register_methods');
            $table->String('is_mobilenumber')->default('required');
        });
    }
}
