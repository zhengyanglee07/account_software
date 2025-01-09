<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateMemberConditionGroupLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_member_condition_group_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();

            $table->unsignedBigInteger('affiliate_member_condition_group_id');
            $table->foreign('affiliate_member_condition_group_id', 'am_cg_levels_am_cg_id_foreign')->references('id')->on('affiliate_member_condition_groups')->onDelete('cascade');
            $table->integer('level');
            $table->decimal('commission_rate')->default(0.00);
            $table->string('commission_type');
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
        Schema::dropIfExists('affiliate_member_condition_group_levels');
    }
}
