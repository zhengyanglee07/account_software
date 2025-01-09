<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliateMemberCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_member_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('reference_key');
            $table->foreignId('account_id')->constrained();
            $table->foreignId('account_domain_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug');
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
        Schema::dropIfExists('affiliate_member_campaigns');
    }
}
