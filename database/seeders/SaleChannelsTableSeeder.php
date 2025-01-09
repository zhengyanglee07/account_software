<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\SaleChannel;

class SaleChannelsTableSeeder extends Seeder
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
                'type' => "funnel",
                'created_at' =>"2021-06-28 14:23:10",
                'updated_at'=>"2021-06-28 14:23:10",
            ],
            [
                'type' => "online-store",
                'created_at' =>"2021-06-28 14:23:10",
                'updated_at'=>"2021-06-28 14:23:10",
            ],
            [
                'type' => "mini-store",
                'created_at' =>"2021-06-28 14:23:10",
                'updated_at'=>"2021-06-28 14:23:10",
            ],
        ];
        foreach($data as $singleData){
            SaleChannel::create($singleData);
        }
    }
}
