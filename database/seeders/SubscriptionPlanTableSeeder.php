<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\SubscriptionPlan;

class SubscriptionPlanTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data=[
            [
                'plan'=>"Free",
            ],
            [
                'plan'=>"Square",
            ],
            [
                'plan'=>"Triangle",
            ],
            [
                'plan'=>"Circle",
            ],
            [
                'plan'=>"Square +",
            ],
            [
                'plan'=>"Triangle +",
            ],
            [
                'plan'=>"Circle +",
            ],



        ];

        foreach ($data as $singleData) {
            SubscriptionPlan::create($singleData);
        }

    }
}
