<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWeightToUsersProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_products', function (Blueprint $table) {
            //
            $table->decimal('weight',9,2)->nullable()->after('reference_key');
            $table->decimal('length',9,2)->nullable()->after('reference_key');
            $table->decimal('width',9,2)->nullable()->after('reference_key');
            $table->decimal('height',9,2)->nullable()->after('reference_key');
            $table->boolean('isFreeShipping')->default(false)->after('reference_key');


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
            $table->dropIfExists('weight');
            $table->dropIfExists('length');
            $table->dropIfExists('width');
            $table->dropIfExists('weight');
            $table->dropIfExists('isFreeShipping');
        });
    }
}
