<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessedContactLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('processed_contact_lessons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('processed_contact_id')->constrained('processed_contacts')->onDelete('cascade');
            $table->foreignId('product_lesson_id')->constrained('product_lessons')->onDelete('cascade');
            $table->integer('progress')->default(0);
            $table->integer('total_watched_duration')->default(0);
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
        Schema::dropIfExists('processed_contact_lessons');
    }
}
