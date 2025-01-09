<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\SubscriptionPlanPrice;

/**
 * Class LocalSubscriptionPlanPriceTableSeeder
 *
 * For LOCAL TESTING ONLY
 */
class LocalSubscriptionPlanPriceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate to reset auto increment id, don't delete
        Schema::disableForeignKeyConstraints();
        DB::table('subscription_plan_prices')->truncate();
        Schema::enableForeignKeyConstraints();

        $data = [
            // free
            [
                'subscription_plan_id' => 1,
                'subscription_plan_type' => 'monthly',
                'price_id' => '',
                'price' => '0.00',
                'payment_type' => 'stripe',
            ],
            [
                'subscription_plan_id' => 1,
                'subscription_plan_type' => 'yearly',
                'price_id' => '',
                'price' => '0.00',
                'payment_type' => 'stripe',
            ],

            // square
            [
                'subscription_plan_id' => 2,
                'subscription_plan_type' => 'monthly',
                'price_id' => 'price_1JbyLUFXu7UEcy2xkO51EXrZ',
                'price' => '29.00',
                'payment_type' => 'stripe',
            ],
            [
                'subscription_plan_id' => 2,
                'subscription_plan_type' => 'yearly',
                'price_id' => 'price_1JbyMLFXu7UEcy2xypfPqMOG',
                'price' => '290.00',
                'payment_type' => 'stripe',
            ],

            // triangle
            [
                'subscription_plan_id' => 3,
                'subscription_plan_type' => 'monthly',
                'price_id' => 'price_1JbyN7FXu7UEcy2xTZu085G9',
                'price' => '49.00',
                'payment_type' => 'stripe',
            ],
            [
                'subscription_plan_id' => 3,
                'subscription_plan_type' => 'yearly',
                'price_id' => 'price_1JbyNOFXu7UEcy2xkUptknAw',
                'price' => '490.00',
                'payment_type' => 'stripe',
            ],

            // circle
            [
                'subscription_plan_id' => 4,
                'subscription_plan_type' => 'monthly',
                'price_id' => 'price_1JbyOMFXu7UEcy2xPkSo2BUG',
                'price' => '99.00',
                'payment_type' => 'stripe',
            ],
            [
                'subscription_plan_id' => 4,
                'subscription_plan_type' => 'yearly',
                'price_id' => 'price_1JbyOWFXu7UEcy2xx02GfAJk',
                'price' => '990.00',
                'payment_type' => 'stripe',
            ],

            // square +
            [
                'subscription_plan_id' => 5,
                'subscription_plan_type' => 'monthly',
                'price_id' => 'price_1JbyxSFXu7UEcy2xx7YNexgg',
                'price' => '199.00',
                'payment_type' => 'stripe',
            ],
            [
                'subscription_plan_id' => 5,
                'subscription_plan_type' => 'yearly',
                'price_id' => 'price_1JbyxSFXu7UEcy2xdF1aFBzP',
                'price' => '1990.00',
                'payment_type' => 'stripe',
            ],

            // triangle +
            [
                'subscription_plan_id' => 6,
                'subscription_plan_type' => 'monthly',
                'price_id' => 'price_1JbyzTFXu7UEcy2xk0zeTfd8',
                'price' => '399.00',
                'payment_type' => 'stripe',
            ],
            [
                'subscription_plan_id' => 6,
                'subscription_plan_type' => 'yearly',
                'price_id' => 'price_1JbyzTFXu7UEcy2xAmrwQpxd',
                'price' => '3990.00',
                'payment_type' => 'stripe',
            ],

            // circle +
            [
                'subscription_plan_id' => 7,
                'subscription_plan_type' => 'monthly',
                'price_id' => 'price_1JOzOzFXu7UEcy2xWDnk8hvr',
                'price' => '799.00',
                'payment_type' => 'stripe',
            ],
            [
                'subscription_plan_id' => 7,
                'subscription_plan_type' => 'yearly',
                'price_id' => 'price_1JOzOzFXu7UEcy2xcVwnxZXx',
                'price' => '7990.00',
                'payment_type' => 'stripe',
            ],
        ];

        foreach ($data as $singleData) {
            SubscriptionPlanPrice::create($singleData);
        }
    }
}
