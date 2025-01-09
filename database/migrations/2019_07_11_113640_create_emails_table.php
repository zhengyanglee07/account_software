<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('sender_id')->unsigned()->nullable();
            $table->foreign('sender_id')->references('id')->on('senders')->onDelete('cascade');
            $table->text('subject')->nullable();
            $table->text('preview_text')->nullable();
            $table->bigInteger('email_design_id')->unsigned()->nullable();
            $table->foreign('email_design_id')->references('id')->on('email_designs')->onDelete('cascade');
            $table->string('segment_id')->nullable();

            $table->string('queue_id')->nullable();
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
        Schema::dropIfExists('emails');
    }
}
