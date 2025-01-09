<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NotificationTableChangeDefaultValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('event_type')->default('product')->change();
            $table->boolean('is_all_selected')->default(true)->change();
            $table->string('display_type')->default('online-store')->change();
            $table->string('layout_type')->default('squared')->change();
            $table->integer('greater_than_time')->default(5)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
