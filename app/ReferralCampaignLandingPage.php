<?php

namespace App;

use App\Page;

use Illuminate\Database\Eloquent\Model;

class ReferralCampaignLandingPage extends Model
{
    protected $guarded = ['id'];

    public function landingPage()
    {
        return Page::find($this->landing_page_id);
    }
}
