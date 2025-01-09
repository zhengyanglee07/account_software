<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->foreignId('sale_channel_id')->constrained()->onDelete('cascade');
            $table->foreignId('funnel_id')->nullable()->constrained()->onDelete('cascade');
            $table->boolean('status')->default(true);
            $table->boolean('is_expiry')->default(false);
            $table->timestamp('active_date')->useCurrent();
            $table->timestamp('end_date')->nullable();
            $table->string('reference_key');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referral_campaigns');
    }
}
