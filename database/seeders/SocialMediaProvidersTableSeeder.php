<?php

namespace Database\Seeders;

use App\SocialMediaProvider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SocialMediaProvidersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate to reset referal campaign actions types, don't delete
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('social_media_providers')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            [
                'name' => 'google'
            ],
            [
                'name' => 'facebook'
            ],
            [
                'name' => 'twitter'
            ],
            [
                'name' => 'linkedin'
            ],
            [
                'name' => 'whatsapp'
            ],
            [
                'name' => 'messenger'
            ],
            [
                'name' => 'telegram'
            ],
            [
                'name' => 'weibo'
            ],
            [
                'name' => 'email'
            ],
        ];

        foreach ($data as $singleData) {
            SocialMediaProvider::updateOrCreate([
                'name' => $singleData['name']
            ],  []);
        }
    }
}
