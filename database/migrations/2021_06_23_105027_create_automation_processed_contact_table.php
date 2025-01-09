<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationProcessedContactTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation_processed_contact', function (Blueprint $table) {
            $table->foreignId('automation_id')->constrained()->onDelete('cascade');
            $table->foreignId('processed_contact_id')->constrained()->onDelete('cascade');
            $table->timestamp('triggered_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('automation_processed_contact');
    }
}
