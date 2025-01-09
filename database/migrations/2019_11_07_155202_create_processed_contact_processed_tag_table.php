<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProcessedContactProcessedTagTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('processed_contact_processed_tag', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('processed_contact_id')->unsigned();
            $table->foreign('processed_contact_id')->references('id')->on('processed_contacts')->onDelete('cascade');
            $table->bigInteger('processed_tag_id')->unsigned();
            $table->foreign('processed_tag_id')->references('id')->on('processed_tags')->onDelete('cascade');
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
        Schema::dropIfExists('processed_contact_processed_tag');
    }
}
