<?php

namespace Database\Seeders;

use App\AutomationProvider;
use Illuminate\Database\Seeder;

class AutomationProvidersTableSeeder extends Seeder
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
                'name' => 'hypershapes'
            ]
        ];

        foreach ($data as $singleData) {
            AutomationProvider::updateOrCreate([
                'name' => $singleData['name']
            ], []);
        }
    }
}
