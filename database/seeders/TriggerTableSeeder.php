<?php

namespace Database\Seeders;

use App\Trigger;
use Illuminate\Database\Seeder;

class TriggerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // don't delete any data or changing their order here
        // only add new data (if got)
        // If you delete any data here a serious problem related
        // to foreign key constraint will occur
        $data = [
            [
                'type' => 'submit_form',
                'name' => 'Submit a Form'
            ],
            [
                'type' => 'purchase_product',
                'name' => 'Purchase product'
            ],
            [
                'type' => 'date_based',
                'name' => 'Date based'
            ],
            [
                'type' => 'add_tag',
                'name' => 'Add a tag'
            ],
            [
                'type' => 'remove_tag',
                'name' => 'Remove a tag'
            ],
            [
                'type' => 'order_spent',
                'name' => 'Order spent'
            ],
            [
                'type' => 'abandon_cart',
                'name' => 'Abandon cart'
            ],
            [
                'type' => 'place_order',
                'name' => 'Place an order'
            ],
            [
                'type' => 'enter_segment',
                'name' => 'Contact enters a segment'
            ],
            [
                'type' => 'exit_segment',
                'name' => 'Contact exits a segment'
            ],
        ];

        foreach ($data as $singleData) {
            Trigger::updateOrCreate([
                'type' => $singleData['type']
            ], $singleData);
        }
    }
}
