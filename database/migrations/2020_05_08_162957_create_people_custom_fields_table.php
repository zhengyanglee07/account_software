<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeopleCustomFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('people_custom_fields', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('processed_contact_id')->unsigned()->nullable();
            $table->foreign('processed_contact_id')->references('id')->on('processed_contacts')->onDelete('cascade');

            $table->bigInteger('account_id')->unsigned()->nullable();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');

            $table->bigInteger('people_custom_field_name_id')->unsigned()->nullable();
            $table->foreign('people_custom_field_name_id')->references('id')->on('people_custom_field_names')->onDelete('cascade');

            // $table->text('people_custom_field_name_id')->nullable();
            $table->text('custom_field_content')->nullable();
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
        Schema::dropIfExists('people_custom_fields');
    }
}
