<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateMemberSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_member_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->boolean('auto_approve_affiliate');
            $table->boolean('auto_approve_commission');
            $table->decimal('auto_approval_period')->default(30);
            $table->boolean('auto_create_account_on_customer_sign_up');
            $table->decimal('minimal_payout')->default(100);
            $table->boolean('enable_lifetime_commission');
            $table->decimal('cookie_expiration_time')->default(90);
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
        Schema::dropIfExists('affiliate_member_settings');
    }
}
