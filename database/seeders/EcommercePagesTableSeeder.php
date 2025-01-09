<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\EcommercePage;
use Illuminate\Support\Facades\DB;

class EcommercePagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('ecommerce_pages')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        /**
         * Do not change the sequence of name in the array here. 
         * Always append new name at the end of array
         */
        $data = [
            'All Products',
            'Shopping Cart',
            'Mini Store Checkout',
            'Checkout Customer Information',
            'Checkout Shipping Methods',
            'Checkout Payment Methods',
            'Checkout Success',
            'Customer Account Signup',
            'Customer Account Login',
            'Customer Account Email Verification',
            'Mini Store Home',
            'All Categories',
            'Customer Account Forgot Password',
        ];

        foreach($data as $singleData){
            EcommercePage::create(['name' => $singleData]);
        }
    }
}
