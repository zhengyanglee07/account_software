<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EcommerceAddressBooksAddShippingCompanyNameAndBillingCompanyNameColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_address_books', function (Blueprint $table) {
            $table->string('shipping_company_name')->nullable()->after('shipping_name');
            $table->string('billing_company_name')->nullable()->after('billing_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecommerce_address_books', function (Blueprint $table) {
            $table->dropColumn('shipping_company_name');
            $table->dropColumn('billing_company_name');
        });
    }
}
