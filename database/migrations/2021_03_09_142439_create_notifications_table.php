<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->string('name');
            $table->string('event_type')->default('product');
            $table->boolean('is_all_selected')->default(true);
            $table->string('display_type')->default('online-store');
            $table->string('layout_type')->default('squared');
            $table->text('message')->nullable();
            $table->string('image_path')->nullable();
            $table->string('mobile_position')->default('bottom');
            $table->string('desktop_position')->default('bottom-left');
            $table->integer('greater_than_time')->default(5);
            $table->boolean('is_anonymous')->default(false);
            $table->string('time_type')->default('Minutes');
            $table->string('reference_key')->nullable();
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
        Schema::dropIfExists('notifications');
    }
}
