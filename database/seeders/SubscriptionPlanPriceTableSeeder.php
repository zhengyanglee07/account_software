<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\SubscriptionPlanPrice;

/**
 * Class SubscriptionPlanPriceTableSeeder
 *
 * For PRODUCTION only, local testing use another seeder
 */
class SubscriptionPlanPriceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('subscription_plan_prices')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $currentEnv = app()->environment();
        if($currentEnv === 'testing') return;
        
        $priceId = [
            'square' => [
                'monthly' => [
                    'local' => 'price_1JbyLUFXu7UEcy2xkO51EXrZ',
                    'staging' => 'price_1KBcAuCuso7duSIEHtlURvc9',
                    'production' => 'price_1IoOoTFXu7UEcy2xlw9FEUMG',
                ],
                'yearly' => [
                    'local' => 'price_1JbyMLFXu7UEcy2xypfPqMOG',
                    'staging' => 'price_1KBcBBCuso7duSIEAair2tTU',
                    'production' => 'price_1Izrb0FXu7UEcy2xSf7J9doY',
                ],
            ],
            'triangle' => [
                'monthly' => [
                    'local' => 'price_1JbyN7FXu7UEcy2xTZu085G9',
                    'staging' => 'price_1KBcC5Cuso7duSIEauijDbak',
                    'production' => 'price_1JU30hFXu7UEcy2x7Rn7u4lN',
                ],
                'yearly' => [
                    'local' => 'price_1JbyNOFXu7UEcy2xkUptknAw',
                    'staging' => 'price_1KBcCICuso7duSIEOQfiuLtX',
                    'production' => 'price_1IzrcJFXu7UEcy2xzGChLZFb',
                ],
            ],
            'circle' => [
                'monthly' => [
                    'local' => 'price_1JbyOMFXu7UEcy2xPkSo2BUG',
                    'staging' => 'price_1KBcDZCuso7duSIEvRWkUwo8',
                    'production' => 'price_1IoOpvFXu7UEcy2xIZjbME5d',
                ],
                'yearly' => [
                    'local' => 'price_1JbyOWFXu7UEcy2xx02GfAJk',
                    'staging' => 'price_1KBcDmCuso7duSIEWDtrs3Rf',
                    'production' => 'price_1IzretFXu7UEcy2xVI7HC4Hp',
                ],
            ],
        ];

        $data = [];
        $plans = [
            'free' => [
                'monthly' => '0.00',
                'yearly' => '0.00',
            ],
            'square' => [
                'monthly' => '29.00',
                'yearly' => '290.00',
            ],
            'triangle' => [
                'monthly' => '49.00',
                'yearly' => '490.00',
            ],
            'circle' => [
                'monthly' => '99.00',
                'yearly' => '990.00',
            ],
        ];

        $index = 0;
        foreach ($plans as $type => $value) {
            $index++;
            foreach ($value as $period => $price) {
                array_push($data, [
                    'subscription_plan_id' => $index,
                    'subscription_plan_type' => $period,
                    'price_id' => $type === 'free' ? '' : $priceId[$type][$period][$currentEnv],
                    'price' => $price,
                    'payment_type' => 'stripe',
                ]);
            }
        }

        foreach ($data as $singleData) {
            SubscriptionPlanPrice::create($singleData);
        }
    }
}
