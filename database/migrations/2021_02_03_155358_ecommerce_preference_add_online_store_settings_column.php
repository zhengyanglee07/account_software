<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EcommercePreferenceAddOnlineStoreSettingsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_preferences', function (Blueprint $table) {
            $table->string('is_fullname')->default('hidden')->after('account_id');
			$table->string('is_mobilenumber')->default('hidden')->after('is_fullname');
			$table->string('is_billingaddress')->default('required')->after('is_mobilenumber');
			$table->string('is_companyname')->default('hidden')->after('is_billingaddress');
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
            $table->dropColumn('is_fullname');
            $table->dropColumn('is_mobilenumber');
            $table->dropColumn('is_billingaddress');
            $table->dropColumn('is_companyname');
        });
    }
}
