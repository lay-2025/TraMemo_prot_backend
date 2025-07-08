<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TravelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('travels')->insert([
            [
                'id' => 1,
                'user_id' => 1,
                'title' => '京都の古都を巡る旅',
                'description' => '京都の伝統的な寺院や庭園を訪れ、日本の歴史と文化に触れる旅。金閣寺、清水寺、伏見稲荷大社など多くの名所を巡りました。秋の紅葉シーズンで、特に東福寺や永観堂の紅葉は圧巻でした。また、祇園では舞妓さんを見かけることもでき、京都の伝統文化を肌で感じることができました。宿泊は東山区の旅館で、朝夕の京料理も楽しみました。',
                'start_date' => '2024-10-15',
                'end_date' => '2024-10-20',
                'visibility' => 1,
                'locationCategory' => 0,
                'prefecture' => 26,
                'country' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'title' => '北海道一周',
                'description' => '夏の北海道ドライブ旅',
                'start_date' => '2025-08-10',
                'end_date' => '2025-08-20',
                'visibility' => 0,
                'locationCategory' => 0,
                'prefecture' => 1,
                'country' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
