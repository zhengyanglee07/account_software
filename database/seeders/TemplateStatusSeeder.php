<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TemplateStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		\DB::table('template_statuses')->insert([
			[
				'name' => 'Draft',
				'created_at' =>"2021-02-14 16:28:22",
                'updated_at'=>"2021-02-14 16:28:22"
			],
			[
				'name' => 'Publish',
				'created_at' =>"2021-02-14 16:28:22",
				'updated_at'=>"2021-02-14 16:28:22"
			]
		]);
    }
}
