<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Account;

class AddIsBoardedIntoAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('is_boarded')->default(false)->after('selected_mini_store');
        });

        $accounts = Account::get();

         foreach($accounts as $account)
         {
            $account->is_boarded= $account->was_selected_goal;
            $account->save();
         }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('is_boarded');
        });
    }
}