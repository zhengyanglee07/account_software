<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleIdToAccountUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_user', function (Blueprint $table) {
            $table->bigInteger('account_role_id')->unsigned()->after('user_id')->default(1);
            $table->foreign('account_role_id')->references('id')->on('account_roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_user', function (Blueprint $table) {
            $table->dropForeign(['account_role_id']);
            $table->dropColumn('account_role_id');
        });
    }
}