<?php

namespace Database\Seeders;

use App\EmailStatus;
use Illuminate\Database\Seeder;

class EmailStatusTableSeeder extends Seeder
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
        $emailStatusData = [
            [
                'status' => "Draft",
            ],
            [
                'status' => "Scheduled",
            ],
            [
                'status' => "Sent",
            ],

            // status below are used in automated mail
            [
                'status' => "In Use",
            ],
            [
                'status' => "Not In Use",
            ],
            [
                'status' => "test",
            ],
        ];

        foreach ($emailStatusData as $singleData) {
            EmailStatus::updateOrCreate([
                'status' => $singleData['status']
            ], []);
        }
    }
}
