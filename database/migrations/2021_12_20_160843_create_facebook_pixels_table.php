<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookPixelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_pixels', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pixel_id');
            $table->longText('api_token');
            $table->foreignId('account_id')->constrained();
            $table->tinyInteger('facebook_selected');
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
        Schema::dropIfExists('facebook_pixels');
    }
}
