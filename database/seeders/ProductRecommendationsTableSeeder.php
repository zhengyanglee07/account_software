<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\ProductRecommendation;
use Illuminate\Support\Facades\DB;

class ProductRecommendationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('product_recommendations')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

         $data = [
            [
                'type' => "category",
                'created_at' =>"2021-06-28 14:23:10",
                'updated_at'=>"2021-06-28 14:23:10",
            ],
            [
                'type' => "frequently-bought-together",
                'created_at' =>"2021-06-28 14:23:10",
                'updated_at'=>"2021-06-28 14:23:10",
            ],
            [
                'type' => "best-selling",
                'created_at' =>"2021-06-28 14:23:10",
                'updated_at'=>"2021-06-28 14:23:10",
            ],
            [
                'type' => "recently-viewed",
                'created_at' =>"2021-06-28 14:23:10",
                'updated_at'=>"2021-06-28 14:23:10",
            ],
        ];
        foreach($data as $singleData){
            ProductRecommendation::create($singleData);
        }
    }
}
