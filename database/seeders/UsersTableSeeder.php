<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
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
                'firstName' => "Steve",
                'lastName' => "Jobs",
                'email' => "steve@gmail.com",
                'email_verified_at' =>"2019-07-02 16:28:53",
                'password' => Hash::make('123123123'),
                'phone' => "12345678",
                'remember_token' => "",
                'created_at' =>"2019-07-02 16:28:22",
                'updated_at'=>"2019-07-02 16:28:53",
            ],
            [
                'firstName' => "Darren",
                'lastName' => "Ter",
                'email' => "darren@gmail.com",
                'email_verified_at' => "2019-07-02 16:28:53",
                'password' => Hash::make('123123123'),
                'phone' => "12345678",
                'remember_token' => "",
                'created_at' => "2019-07-02 16:28:22",
                'updated_at' => "2019-07-02 16:28:53",
            ],
            [
                'firstName' => "Zheng Yang",
                'lastName' => "",
                'email' => "zhengyang@gmail.com",
                'email_verified_at' => "2019-07-02 16:28:53",
                'password' => Hash::make('123123123'),
                'phone' => "12345678",
                'remember_token' => "",
                'created_at' => "2019-07-02 16:28:22",
                'updated_at' => "2019-07-02 16:28:53",
            ],
            [
                'firstName' => "Huai Jie",
                'lastName' => "Khaw",
                'email' => "khawsora@gmail.com",
                'email_verified_at' =>"2019-07-02 16:28:53",
                'password' => Hash::make('123123123'),
                'phone' => "12345678",
                'remember_token' => "4ZP5bFSTKODNYCaeQvgtHnhzWednIacHfqV08MK7UrazC7hUzrsvAsXbr5MA",
                'created_at' =>"2019-07-02 16:28:22",
                'updated_at'=>"2019-07-02 16:28:53",
            ],
        ];

        foreach ($data as $singleData) {
            User::create($singleData);
        }
    }
}
