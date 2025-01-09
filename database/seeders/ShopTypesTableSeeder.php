<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\ShopType;

class ShopTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $data = [
            [
                'type' => "Shopify",
                'created_at' =>"2019-07-02 16:28:22",
                'updated_at'=>"2019-07-02 16:28:53",
            ],
            [
                'type' => "WooCommerce",
                'created_at' =>"2019-07-02 16:28:22",
                'updated_at'=>"2019-07-02 16:28:53",
            ]
        ];
        foreach($data as $singleData){
            ShopType::create($singleData);
        }
    }
}
