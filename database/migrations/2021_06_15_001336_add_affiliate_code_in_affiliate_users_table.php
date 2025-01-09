<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAffiliateCodeInAffiliateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('affiliate_users', function (Blueprint $table) {
            $table->string('address')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('state')->nullable()->change();
            $table->string('zipcode')->nullable()->change();
            $table->string('country')->nullable()->change();
            $table->string('paypal_email')->nullable()->change();
            $table->string('affiliate_code')->nullable()->after('affiliate_unique_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('affiliate_users', function (Blueprint $table) {
            $table->dropColumn('affiliate_code');
            // $table->string('address')->nullable(false)->change();
            // $table->string('city')->nullable(false)->change();
            // $table->string('state')->nullable(false)->change();
            // $table->string('zipcode')->nullable(false)->change();
            // $table->string('country')->nullable(false)->change();
            // $table->string('paypal_email')->nullable(false)->change();
        });
    }
}
