<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeColumnIntoBadgesUsersProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('badges_users_product', function (Blueprint $table) {
            $table->string('type')->nullable()->after('users_product_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('badges_users_product', function (Blueprint $table) {
            $table->string('type')->nullable()->after('users_product_id');
        });
    }
}
