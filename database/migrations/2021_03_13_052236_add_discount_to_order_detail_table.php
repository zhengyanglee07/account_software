<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountToOrderDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->integer('discount')->default(0)->nullable()->after('parent_id');
            $table->json('discount_details')->nullable()->after('parent_id');
            $table->boolean('is_discount_applied')->default(false)->after('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('discount');
            $table->dropColumn('discount_details');
            $table->dropColumn('is_dicount_applied');
        });
    }
}
