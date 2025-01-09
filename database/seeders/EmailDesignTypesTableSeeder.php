<?php

namespace Database\Seeders;

use App\EmailDesignType;
use Illuminate\Database\Seeder;

class EmailDesignTypesTableSeeder extends Seeder
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
                'name' => "builtin-template",
            ],
            [
                'name' => "user-template",
            ],
            [
                // default type just simply means non-template email design
                'name' => "default",
            ]
        ];

        foreach ($data as $singleData) {
            EmailDesignType::updateOrCreate([
                'name' => $singleData['name']
            ], []);
        }
    }
}
