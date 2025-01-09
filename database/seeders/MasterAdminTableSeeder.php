<?php

namespace Database\Seeders;

use App\MasterAdmin\MasterAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MasterAdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		DB::table('master_admins')->insert([
			'firstName' => "Gabriel",
			'lastName' => "Tan",
			'username' => "hypershapes@gabriel",
			'password' => Hash::make('123123123'),
			'created_at' =>"2019-07-02 16:28:22",
			'updated_at'=>"2019-07-02 16:28:53",

		]);

		DB::table('master_admins')->insert([
			'firstName' => "Steve",
			'lastName' => "",
			'username' => "hypershapes@steve",
			'password' => Hash::make('123123123'),
			'created_at' =>"2019-07-02 16:28:22",
			'updated_at'=>"2019-07-02 16:28:53",
		]);

		DB::table('master_admins')->insert([
			'firstName' => "Tommy",
			'lastName' => "Liew",
			'username' => "hypershapes@tommy",
			'password' => Hash::make('123123123'),
			'created_at' =>"2019-07-02 16:28:22",
			'updated_at'=>"2019-07-02 16:28:53",
		]);

		DB::table('master_admins')->insert([
			'firstName' => "Darren",
			'lastName' => "Ter",
			'username' => "hypershapes@darren",
			'password' => Hash::make('123123123'),
			'created_at' =>"2019-07-02 16:28:22",
			'updated_at'=>"2019-07-02 16:28:53",
		]);

		DB::table('master_admins')->insert([
			'firstName' => "Andy",
			'lastName' => "Tan",
			'username' => "hypershapes@andy",
			'password' => Hash::make('123123123'),
			'created_at' =>"2019-07-02 16:28:22",
			'updated_at'=>"2019-07-02 16:28:53",
		]);

		DB::table('master_admins')->insert([
			'firstName' => "Yee Kang",
			'lastName' => "Teo",
			'username' => "hypershapes@yeekang",
			'password' => Hash::make('123123123'),
			'created_at' =>"2019-07-02 16:28:22",
			'updated_at'=>"2019-07-02 16:28:53",
		]);

		DB::table('master_admins')->insert([
			'firstName' => "Jaylyn",
			'lastName' => "",
			'username' => "hypershapes@jaylyn",
			'password' => Hash::make('123123123'),
			'created_at' =>"2020-07-30 11:28:53",
			'updated_at'=>"2020-07-30 11:28:53",
		]);

		DB::table('master_admins')->insert([
			'firstName' => "Chee Jun",
			'lastName' => "",
			'username' => "hypershapes@cheejun",
			'password' => Hash::make('123123123'),
			'created_at' =>"2020-07-30 11:28:53",
			'updated_at'=>"2020-07-30 11:28:53",
		]);


    }
}
