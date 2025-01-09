<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateMemberLifetimeCommissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_member_lifetime_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();

            $table->unsignedBigInteger('processed_contact_id');
            $table->foreign('processed_contact_id', 'lifetime_commissions_processed_contact_id_foreign')->references('id')->on('processed_contacts')->onDelete('cascade');

            $table->unsignedBigInteger('affiliate_member_program_id');
            $table->foreign('affiliate_member_program_id', 'lifetime_commissions_program_id_foreign')->references('id')->on('affiliate_member_programs')->onDelete('cascade');

            $table->unsignedBigInteger('affiliate_member_account_id');
            $table->foreign('affiliate_member_account_id', 'lifetime_commissions_account_id_foreign')->references('id')->on('affiliate_member_accounts')->onDelete('cascade');

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
        Schema::dropIfExists('affiliate_member_lifetime_commissions');
    }
}
