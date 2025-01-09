<?php

namespace Database\Seeders;

use App\AutomationStatus;
use Illuminate\Database\Seeder;

class AutomationStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // IMPORTANT: don't delete any data or changing their order here
        // only add new data (if got)
        // If you delete any data here a serious problem related
        // to foreign key constraint will occur
        $data = [
            [
                'name' => 'draft',
            ],
            [
                'name' => 'activated',
            ],
            [
                'name' => 'paused'
            ]
        ];

        foreach ($data as $singleData) {
            AutomationStatus::updateOrCreate([
                'name' => $singleData['name']
            ], []);
        }
    }
}
