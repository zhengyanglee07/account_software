<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\App;
use DB;

class AppsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('apps')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        $data = [
            ['type' => "delyva"],
            ['type' => "easyparcel"],
            ['type' => "lalamove"],
            ['type' => "facebook"],
            ['type' => "cashback"],
            ['type' => "product_recommendation"],
            ['type' => "product_reviews"],

        ];
        foreach ($data as $singleData) {
            App::create($singleData);
        }
    }
}
