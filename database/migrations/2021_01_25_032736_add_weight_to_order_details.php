<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWeightToOrderDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->decimal('weight',9,2)->after('is_taxable')->default(0.00);
            $table->decimal('height',9,2)->after('weight')->default(0.00);
            $table->decimal('width',9,2)->after('weight')->default(0.00);
            $table->decimal('length',9,2)->after('weight')->default(0.00);

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
            $table->dropColumn('weight');
            $table->dropColumn('height');
            $table->dropColumn('width');
            $table->dropColumn('length');
        });
    }
}
