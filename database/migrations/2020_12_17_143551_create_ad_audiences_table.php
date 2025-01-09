<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdAudiencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad_audiences', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('segment_id')->unsigned();
            $table->foreign('segment_id')->references('id')->on('segments')->onDelete('cascade');

            $table->bigInteger('account_oauth_id')->unsigned();
            $table->foreign('account_oauth_id')->references('id')->on('account_oauths')->onDelete('cascade');

            $table->string('list_name')->nullable();
            $table->string('list_id')->nullable();
            $table->string('sync_status')->default('pending');

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
        Schema::dropIfExists('ad_audiences');
    }
}
