<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateMemberProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_member_programs', function (Blueprint $table) {
            $table->id();
            $table->string('reference_key');
            $table->foreignId('account_id')->constrained();
            $table->string('title');
            $table->string('domain');
            $table->boolean('auto_create_account_on_customer_sign_up');
            $table->boolean('auto_approve_affiliate');
            $table->boolean('enable_lifetime_commission');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliate_member_programs');
    }
}
