<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProductSubscriptionAddReferenceKeyTypeFixedPriceAndRateColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_subscriptions', function (Blueprint $table) {
            $table->string('reference_key')->nullable()->after('expiration_cycle');
            $table->string('type')->default('none')->after('description');
            $table->decimal('discount_rate')->default(0)->after('type');
            $table->decimal('capped_at')->default(0)->after('discount_rate');
            $table->decimal('fixed_price')->default(0)->after('capped_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_subscriptions', function (Blueprint $table) {
            $table->dropColumn('reference_key');
            $table->dropColumn('type');
            $table->dropColumn('discount_rate');
            $table->dropColumn('capped_at');
            $table->dropColumn('fixed_price');
        });
    }
}
