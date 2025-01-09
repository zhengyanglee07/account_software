<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeVariationOriginalPriceNullableFromVariantDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('variant_details', function (Blueprint $table) {
            $table->decimal('comparePrice', 9, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('variant_details', function (Blueprint $table) {
            $table->decimal('comparePrice', 9, 2)->default(0.00)->change();
        });
    }
}
