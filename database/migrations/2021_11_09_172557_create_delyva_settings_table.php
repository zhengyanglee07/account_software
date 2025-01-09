<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDelyvaSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delyva_settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->unsigned();
            $table->String('delyva_company_code');
            $table->String('delyva_company_id');
            $table->String('delyva_user_id');
            $table->string('delyva_customer_id');
            $table->string('delyva_api');
            $table->boolean('delyva_selected')->default(false);
            $table->timestamps();
            $table->foreign('account_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delyva_settings');
    }
}
