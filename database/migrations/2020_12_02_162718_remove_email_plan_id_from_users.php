<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveEmailPlanIdFromUsers extends Migration
{
    private function emailPlansTableExists(): bool
    {
        return Schema::hasTable('email_plans');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if ($this->emailPlansTableExists()) {
                $table->dropForeign('users_email_plan_id_foreign');
                $table->dropColumn('email_plan_id');
            }
        });

        if ($this->emailPlansTableExists()) {
            Schema::drop('email_plans');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // nil
    }
}
