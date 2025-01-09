<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWasSelectedGoalColumnIntoAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        if (Schema::hasColumn('accounts', 'was_selected_goal')) {
            Schema::table('accounts', function (Blueprint $table){
              $table->dropColumn('was_selected_goal');
           });
       }
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('was_selected_goal')->default(true)->after('was_educated');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('was_selected_goal');
        });
    }
}
