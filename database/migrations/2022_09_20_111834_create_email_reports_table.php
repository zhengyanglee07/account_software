<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_reports', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('email_id')->unsigned();
            $table->foreign('email_id')->references('id')->on('emails')->onDelete('cascade');
            $table->bigInteger('total_sent')->default(0);
            $table->bigInteger('total_opened')->default(0);
            $table->bigInteger('total_clicked')->default(0);
            $table->bigInteger('total_bounced')->default(0);
            $table->bigInteger('total_unsubscribed')->default(0);
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
        Schema::dropIfExists('email_reports');
    }
}
