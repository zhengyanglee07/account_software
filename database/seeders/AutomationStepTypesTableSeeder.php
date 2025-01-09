<?php

namespace Database\Seeders;

use App\AutomationStepType;
use Illuminate\Database\Seeder;

class AutomationStepTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'type' => 'delay',
                'name' => 'Delay',
            ],
            [
                'type' => 'action',
                'name' => 'Action'
            ],
            [
                'type' => 'decision',
                'name' => 'Decision'
            ],
            [
                'type' => 'exit',
                'name' => 'Exit'
            ]
        ];

        foreach ($data as $singleData) {
            AutomationStepType::updateOrCreate([
                'type' => $singleData['type']
            ], $singleData);
        }
    }
}
