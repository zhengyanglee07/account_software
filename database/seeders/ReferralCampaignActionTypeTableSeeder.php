<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\ReferralCampaignActionType;
use Illuminate\Support\Facades\DB;

class ReferralCampaignActionTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate to reset referal campaign actions types, don't delete
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('referral_campaign_action_types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            [
                'type' => "purchase",
            ],
            [
                'type' => "sign-up",
            ],
            [
                'type' => "join",
            ],
            [
                'type' => "custom",
            ],

        ];
        foreach($data as $singleData){
            ReferralCampaignActionType::create($singleData);
        }
    }
}
