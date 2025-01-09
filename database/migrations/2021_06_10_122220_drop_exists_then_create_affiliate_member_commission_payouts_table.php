<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropExistsThenCreateAffiliateMemberCommissionPayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('affiliate_member_commission_payments'); // table in v1
        Schema::dropIfExists('affiliate_member_commission_payouts');

        Schema::create('affiliate_member_commission_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('affiliate_member_participant_id');
            $table->foreign('affiliate_member_participant_id', 'am_commission_payments_new_participant_id_foreign')->references('id')->on('affiliate_member_participants')->onDelete('cascade');

            $table->string('status');
            $table->timestamp('paid_at');
            $table->string('currency');
            $table->decimal('amount')->default(0.00);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliate_member_commission_payments'); // table in v1
        Schema::dropIfExists('affiliate_member_commission_payouts');

        Schema::create('affiliate_member_commission_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('affiliate_member_program_participant_id');
            $table->foreign('affiliate_member_program_participant_id', 'affiliate_member_commission_payments_participant_id_foreign')->references('id')->on('affiliate_member_program_participants')->onDelete('cascade');

            $table->timestamp('paid_at');
            $table->decimal('amount')->default(0.00);
            $table->string('currency');

            $table->softDeletes();
            $table->timestamps();
        });
    }
}
