<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\ReferralCampaignRewardType;
use Illuminate\Support\Facades\DB;

class ReferralCampaignRewardTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate to reset referal campaign reward types, don't delete
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('referral_campaign_reward_types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        //
        $data = [
            [
                'type' => "promo-code",
            ],
            [
                'type' => "downloadable-content",
            ],
            [
                'type' => "custom-message",
            ],
        ];
        foreach($data as $singleData){
            ReferralCampaignRewardType::create($singleData);
        }
    }
}
