<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateMemberProgramLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_member_program_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();

            $table->unsignedBigInteger('affiliate_member_program_id');
            $table->foreign('affiliate_member_program_id', 'program_levels_program_id_foreign')->references('id')->on('affiliate_member_programs')->onDelete('cascade');

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
        Schema::dropIfExists('affiliate_member_program_levels');
    }
}
