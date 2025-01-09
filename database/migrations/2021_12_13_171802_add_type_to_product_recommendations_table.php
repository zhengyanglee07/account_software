<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToProductRecommendationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_recommendations', function($table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn(['account_id']);
            $table->dropColumn('status');
            $table->string('type')->after('id');
         });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_recommendations', function($table) {
            $table->foreignId('account_id')->constrained();
            $table->boolean('status')->default(true);
            $table->dropColumn('type');
         });
    }
}
